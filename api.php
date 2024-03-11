<?php

$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestMethod) {
    case 'GET':
        
        $data = getDataFromDatabase();
        if ($data) {
            echo json_encode(['method' => 'GET', 'data' => $data]);
        } else {
            http_response_code(500); 
            echo json_encode(['error' => 'Failed to fetch data from the database']);
        }
        break;

    case 'POST':    
        $postData = json_decode(file_get_contents('php://input'), true);
        if ($postData) {
                $postData = array(
                    "title" => $postData["title"],
                    "message" => $postData["message"],
                );
            $success = insertDataIntoDatabase($postData);
            if ($success) {
                echo json_encode(['method' => 'POST', 'message' => 'Data inserted successfully']);
            } else {
                http_response_code(500); 
                echo json_encode(['error' => 'Failed to insert data into the database']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid POST data']);
        }
        break;

    default:

        http_response_code(405); 
        echo json_encode(['error' => 'Unsupported request method']);
        break;
}


function getDataFromDatabase()
{

    $conn = mysqli_connect('localhost', 'root', '', 'testdb', 3308);

    if (!$conn) {

        return false;
    }


    $query = "SELECT * FROM testpost";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        mysqli_close($conn);
        return false;
    }

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    mysqli_close($conn);

    return $data;
}

function insertDataIntoDatabase($data)
{
    $conn = mysqli_connect('localhost', 'root', '', 'testdb', 3308);

    if (!$conn) {
        return false;
    }

    $title = mysqli_real_escape_string($conn, $data['title']);
    $message = mysqli_real_escape_string($conn, $data['message']);

    $query = "INSERT INTO testpost (title, message) VALUES ('$title', '$message')";


    $success = mysqli_query($conn, $query);

    mysqli_close($conn);

    return $success;
}

