<?php
include 'includes/db_connect.php';

if(isset($_GET['state_id'])) {
    $stateId = $_GET['state_id'];
    
    $sql = "SELECT * FROM districts WHERE state_id = ? ORDER BY name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $stateId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $districts = [];
    while($row = $result->fetch_assoc()) {
        $districts[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($districts);
}
?>