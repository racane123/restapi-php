<?php

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

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
                "name" => $postData["name"],
                "title" => $postData['title'],
                "area" => $postData["area"],
                "price" => $postData["price"],
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

    case 'PUT':
        $putData = json_decode(file_get_contents('php://input'), true);

        if ($putData) {
            $putData = array(
                "id" => $putData["id"],
                "name" => $putData["name"],
                "title" => $putData["title"],
                "area" => $putData["area"],
                "price" => $putData["price"],
            );
            $success = updateDataInDatabase($putData);
            if ($success) {
                echo json_encode(['method' => 'PUT', 'message' => 'Data updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update data in the database']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid PUT data']);
        }
        break;

    case 'DELETE':
        $deleteId = $_GET["id"]; // Retrieve ID from query string
        $success = deleteDataFromDatabase($deleteId);
        if ($success) {
            echo json_encode(['method' => 'DELETE', 'message' => 'Data deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete data from the database']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Unsupported request method']);
        break;
}

function getDataFromDatabase()
{
    $conn = mysqli_connect('localhost', 'root', '', 'testdb', 3306);

    if (!$conn) {
        return false;
    }

    $query = "SELECT * FROM landcomp";
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
    $conn = mysqli_connect('localhost', 'root', '', 'testdb', 3306);

    if (!$conn) {
        return false;
    }

    $name = mysqli_real_escape_string($conn, $data['name']);
    $title = mysqli_real_escape_string($conn, $data['title']);
    $area = mysqli_real_escape_string($conn, $data['area']);
    $price = mysqli_real_escape_string($conn, $data['price']);

    $query = "INSERT INTO landcomp (name, title, area, price) VALUES ('$name','$title', '$area', '$price')";

    $success = mysqli_query($conn, $query);

    mysqli_close($conn);

    return $success;
}

function updateDataInDatabase($data)
{
    $conn = mysqli_connect('localhost', 'root', '', 'testdb', 3306);

    if (!$conn) {
        return false;
    }

    $id = mysqli_real_escape_string($conn, $data['id']);
    $name = mysqli_real_escape_string($conn, $data['name']);
    $title = mysqli_real_escape_string($conn, $data['title']);
    $area = mysqli_real_escape_string($conn, $data['area']);
    $price = mysqli_real_escape_string($conn, $data['price']);

    $query = "UPDATE landcomp SET name='$name', title='$title', area='$area', price='$price' WHERE id='$id'";

    $success = mysqli_query($conn, $query);

    mysqli_close($conn);

    return $success;
}

function deleteDataFromDatabase($id)
{
    $conn = mysqli_connect('localhost', 'root', '', 'testdb', 3306);

    if (!$conn) {
        return false;
    }

    $id = mysqli_real_escape_string($conn, $id);

    $query = "DELETE FROM landcomp WHERE id='$id'";

    $success = mysqli_query($conn, $query);

    mysqli_close($conn);

    return $success;
}
