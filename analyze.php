<?php 
include 'includes/header.php';
include 'includes/db_connect.php';

// Fetch states for dropdown
$statesQuery = "SELECT * FROM states ORDER BY name";
$statesResult = $conn->query($statesQuery);

// Fetch crops for dropdown
$cropsQuery = "SELECT * FROM crops ORDER BY name";
$cropsResult = $conn->query($cropsQuery);
?>

<!-- Page Header -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-4 mb-md-0">
                <h1 class="fw-bold">Soil Analysis</h1>
                <p class="text-muted mb-0">Enter your soil test values to get personalized fertilizer recommendations</p>
            </div>
            <div class="col-md-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-md-end mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Analyze Soil</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Analysis Form Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="card-title text-center mb-4">Enter Soil Test Values</h3>
                        
                        <form id="analyzeForm" action="results.php" method="POST">
                            <div class="row g-4">
                                <!-- NPK Values Section -->
                                <div class="col-md-12">
                                    <h5 class="border-bottom pb-2 mb-3">NPK Values (mg/kg)</h5>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="nitrogen" class="form-label">Nitrogen (N)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control npk-input" id="nitrogen" name="nitrogen" placeholder="Enter N value" step="0.01" min="0" data-display="nitrogenDisplay" required>
                                        <span class="input-group-text">mg/kg</span>
                                    </div>
                                    <div id="nitrogenDisplay" class="conversion-display mt-2">0.00 kg/ha</div>
                                    <div class="invalid-feedback">Please enter a valid nitrogen value.</div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="phosphorus" class="form-label">Phosphorus (P)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control npk-input" id="phosphorus" name="phosphorus" placeholder="Enter P value" step="0.01" min="0" data-display="phosphorusDisplay" required>
                                        <span class="input-group-text">mg/kg</span>
                                    </div>
                                    <div id="phosphorusDisplay" class="conversion-display mt-2">0.00 kg/ha</div>
                                    <div class="invalid-feedback">Please enter a valid phosphorus value.</div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="potassium" class="form-label">Potassium (K)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control npk-input" id="potassium" name="potassium" placeholder="Enter K value" step="0.01" min="0" data-display="potassiumDisplay" required>
                                        <span class="input-group-text">mg/kg</span>
                                    </div>
                                    <div id="potassiumDisplay" class="conversion-display mt-2">0.00 kg/ha</div>
                                    <div class="invalid-feedback">Please enter a valid potassium value.</div>
                                </div>
                                
                                <!-- pH Value -->
                                <div class="col-md-6">
                                    <label for="ph" class="form-label">Soil pH</label>
                                    <input type="number" class="form-control" id="ph" name="ph" placeholder="Enter pH value (1-14)" step="0.1" min="0" max="14" required>
                                    <div class="invalid-feedback">Please enter a valid pH value between 0 and 14.</div>
                                </div>
                                
                                <!-- Plot Area -->
                                <div class="col-md-6">
                                    <label for="plotArea" class="form-label">Plot Area</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="plotArea" name="plotArea" placeholder="Enter area" step="0.01" min="0.01" required>
                                        <select class="form-select" id="areaUnit" name="areaUnit" style="max-width: 120px;">
                                            <option value="hectare">Hectare</option>
                                            <option value="acre">Acre</option>
                                        </select>
                                    </div>
                                    <div class="invalid-feedback">Please enter a valid plot area.</div>
                                </div>
                                
                                <!-- Location & Crop Section -->
                                <div class="col-md-12">
                                    <h5 class="border-bottom pb-2 mb-3">Location & Crop Details</h5>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="state" class="form-label">State</label>
                                    <select class="form-select" id="state" name="state" required>
                                        <option value="">Select State</option>
                                        <?php while($state = $statesResult->fetch_assoc()): ?>
                                            <option value="<?php echo $state['id']; ?>"><?php echo $state['name']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a state.</div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="district" class="form-label">District</label>
                                    <select class="form-select" id="district" name="district" required>
                                        <option value="">Select District</option>
                                        <!-- Districts will be populated via JavaScript -->
                                    </select>
                                    <div class="invalid-feedback">Please select a district.</div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label for="crop" class="form-label">Crop</label>
                                    <select class="form-select" id="crop" name="crop" required>
                                        <option value="">Select Crop</option>
                                        <?php while($crop = $cropsResult->fetch_assoc()): ?>
                                            <option value="<?php echo $crop['id']; ?>"><?php echo $crop['name']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a crop.</div>
                                </div>
                                
                                <!-- Submit Button -->
                                <div class="col-12 text-center mt-4">
                                    <button type="submit" class="btn btn-success btn-lg px-5">Analyze Soil</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Create a PHP file to handle district fetching via AJAX -->
<?php
// Create get_districts.php file
$get_districts_content = '<?php
include \'includes/db_connect.php\';

if(isset($_GET[\'state_id\'])) {
    $stateId = $_GET[\'state_id\'];
    
    $sql = "SELECT * FROM districts WHERE state_id = ? ORDER BY name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $stateId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $districts = [];
    while($row = $result->fetch_assoc()) {
        $districts[] = [
            \'id\' => $row[\'id\'],
            \'name\' => $row[\'name\']
        ];
    }
    
    header(\'Content-Type: application/json\');
    echo json_encode($districts);
}
?>';

file_put_contents('get_districts.php', $get_districts_content);
?>

<!-- Tips Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="card-title mb-4">Tips for Accurate Soil Analysis</h4>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 text-success">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5>Proper Soil Sampling</h5>
                                        <p class="text-muted">Collect soil samples from multiple locations in your field at a depth of 15-20 cm for accurate results.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 text-success">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5>Recent Test Results</h5>
                                        <p class="text-muted">Use soil test results that are less than 1 year old for the most accurate recommendations.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 text-success">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5>Accurate Measurements</h5>
                                        <p class="text-muted">Ensure your NPK values are in mg/kg as provided by the soil testing lab for accurate conversion.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 text-success">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5>Regular Testing</h5>
                                        <p class="text-muted">Test your soil at least once a year to track changes and adjust your fertilizer strategy accordingly.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>