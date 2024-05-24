<?php

// Function to find the destination ID by name
function find_destination_id_by_name($destinations, $name)
{
    foreach ($destinations as $destination) {
        if (isset($destination['destinationName']) && strcasecmp($destination['destinationName'], $name) == 0) {
            return strval($destination['destinationId']); // Return the destinationId
        }
    }
    return null; // Return null if no match is found
}

// Function to fetch Viator events
function get_viator_events($destination_name, $datefrom, $dateend)
{
    // API endpoints
    $taxonomyEndpoint = 'https://api.sandbox.viator.com/partner/v1/taxonomy/destinations';
    $searchEndpoint = 'https://api.sandbox.viator.com/partner/products/search';

    // API headers
    $headers = [
        'exp-api-key: 7ab376a6-0808-45fe-b51f-d25d709efd15',
        'Accept-Language: en-US',
        'Accept: application/json;version=2.0'
    ];

    // Initialize cURL session to fetch destinations data
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $taxonomyEndpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/json']));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($ch);

    // Handle cURL error
    if ($e = curl_error($ch)) {
        echo $e;
        return;
    }

    // Decode destinations data
    $decoded = json_decode($resp, true);
    curl_close($ch);

    // Extract destinations array
    $destinations_array = isset($decoded['data']) ? $decoded['data'] : [];

    // Find destination ID
    $destination_id = find_destination_id_by_name($destinations_array, $destination_name);

    if ($destination_id !== null) {
        // Define the request body
        $request_body = [
            "filtering" => [
                "startDate" => $datefrom,
                "endDate" => $dateend,
                "destination" => $destination_id
            ],
            "currency" => "USD"
        ];

        // Convert the request body array to JSON
        $request_body_json = json_encode($request_body);

        // Initialize cURL session to search for events
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $searchEndpoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/json']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body_json);

        // Execute the cURL request
        $resp = curl_exec($ch);
        // Close cURL session
        curl_close($ch);

        return json_decode($resp, true);
    } else {
        echo "Error: Destination not found.";
    }
}

// Example usage
$destination = "Los Angeles";
$datefrom = "2024-05-24";
$dateend = "2024-06-29";
$events_array = get_viator_events($destination, $datefrom, $dateend);


for ($i = 0; $i < 1; $i++) {

    print_r($events_array['products'][$i]);
};
