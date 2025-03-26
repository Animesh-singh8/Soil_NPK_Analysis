<?php
// Convert NPK from mg/kg to kg/ha
function convertToKgPerHa($value) {
    // Assuming 1 mg/kg in the top 15 cm soil layer corresponds to ~2.24 kg/ha
    return $value * 2.24;
}

// Calculate fertilizer requirement
function calculateFertilizer($measured, $required, $nutrientPercent) {
    if ($measured >= $required) {
        return 0; // No deficiency
    }
    
    $deficiency = $required - $measured;
    return ($deficiency * 100) / $nutrientPercent;
}

// Get crop requirements from database
function getCropRequirements($conn, $cropId, $stateId, $districtId) {
    $sql = "SELECT * FROM crop_requirements 
            WHERE crop_id = ? AND state_id = ? AND district_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $cropId, $stateId, $districtId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        // Fallback to state level if district specific data not available
        $sql = "SELECT * FROM crop_requirements 
                WHERE crop_id = ? AND state_id = ? LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $cropId, $stateId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            // Return default values if no data available
            return [
                'required_n' => 120,
                'required_p' => 60,
                'required_k' => 40,
                'min_ph' => 6.0,
                'max_ph' => 7.5
            ];
        }
    }
}

// Get fertilizer data from database
function getFertilizerData($conn) {
    $sql = "SELECT * FROM fertilizers";
    $result = $conn->query($sql);
    
    $fertilizers = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $fertilizers[] = $row;
        }
    }
    
    return $fertilizers;
}

// Get crop name by ID
function getCropName($conn, $cropId) {
    $sql = "SELECT name FROM crops WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cropId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['name'];
    } else {
        return "Unknown Crop";
    }
}

// Get state name by ID
function getStateName($conn, $stateId) {
    $sql = "SELECT name FROM states WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $stateId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['name'];
    } else {
        return "Unknown State";
    }
}

// Get district name by ID
function getDistrictName($conn, $districtId) {
    $sql = "SELECT name FROM districts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $districtId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['name'];
    } else {
        return "Unknown District";
    }
}

// Get pH status
function getPHStatus($measured, $min, $max) {
    if ($measured < $min) {
        return "Acidic";
    } else if ($measured > $max) {
        return "Alkaline";
    } else {
        return "Optimal";
    }
}

// Get pH recommendation
function getPHRecommendation($measured, $min, $max) {
    if ($measured < $min) {
        return "Apply agricultural lime to increase soil pH.";
    } else if ($measured > $max) {
        return "Apply agricultural sulfur or gypsum to decrease soil pH.";
    } else {
        return "pH is in optimal range. No adjustment needed.";
    }
}
?>