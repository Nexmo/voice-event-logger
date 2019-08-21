<?php

require __DIR__ . '/../vendor/autoload.php';

// pre-app setup, inc. loading env vars on development platforms
require __DIR__ . '/../setup.php';

$app = new Slim\App();

// configure logging etc
require __DIR__ . '/../config.php';


// temporary answer endpoint for testing purposes
$app->get('/webhooks/answer', function ($request, $response, $args) {
    $ncco = [
        ["action" => "talk", "text" => "Hello and welcome"]
    ];
    return $response->withJson($ncco);
});

// event logger
$app->map(['GET', 'POST'], '/event', function ($request, $response, $args) {
    $get_params = $request->getQueryParams();
    $post_params = $request->getParsedBody();

    $input_params = $get_params;

    if (is_array($post_params)) {
        $input_params = array_merge($input_params, $post_params);
    }

    $status = "unknown";
    if(isset($input_params['status'])) {
        $status = $input_params['status'];
    } elseif (isset($input_params['dtmf'])) {
        $status = "dtmf";
    } elseif (isset($input_params['recording_uuid'])) {
        $status = "recording";
    }

    $this->logger->info("Event: " . $status, $input_params);
    return $response->getBody()->write("OK");
});

// event viewer with very rudimentary log parsing
$app->get('/', function ($request, $response, $args) {
    // everything here will probably fall over with non-trivial amounts of data
    if ($redis_url = getenv('REDIS_URL')) {
        $redis = new Predis\Client($redis_url);
        $data = $redis->lrange("logs", 0, -1);
    } elseif ($log_file = getenv('LOG_FILE')) {
        $all_data = file_get_contents($log_file);
        $data = explode("\n", $all_data);
    } else {
        return $response->getBody()->write("Cannot retrieve event data for this storage type");
    }

    if($data) {
        $output_data = [];
        foreach (array_reverse($data) as $row) {
            // parse date and time, status, and data fields
            $pattern = '/^\[(.*?)\].*?Event: (.*?) ({.*?})/';
            $matches = [];
            preg_match($pattern, $row, $matches);

            $formatted_row = [];
            $formatted_row['date'] = new DateTime($matches[1]);
            $formatted_row['status'] = $matches[2];

            // format the fields
            $json_body = json_decode($matches[3], true);
            $formatted_row['data_fields'] = '';
            foreach($json_body as $field => $value) {
                $formatted_row['data_fields'] .= $field . ": " . $value . "<br />\n";
            }

            $output_data[] = $formatted_row;
        }

        return $this->view->render($response, "view.html", ["data" => $output_data]);
    }
    return $response->getBody()->write("No logs received yet");
});

$app->run();
