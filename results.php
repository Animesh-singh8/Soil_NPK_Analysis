<?php
include 'includes/header.php';
include 'includes/db_connect.php';
include 'includes/functions.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: analyze.php');
    exit;
}

// Get form data
$nitrogen_mg_kg = floatval($_POST['nitrogen']);
$phosphorus_mg_kg = floatval($_POST['phosphorus']);
$potassium_mg_kg = floatval($_POST['potassium']);
$ph = floatval($_POST['ph']);
$plotArea = floatval($_POST['plotArea']);
$areaUnit = $_POST['areaUnit'];
$stateId = intval($_POST['state']);
$districtId = intval($_POST['district']);
$cropId = intval($_POST['crop']);

// Convert area to hectares if needed
$plotAreaHa = $plotArea;
if ($areaUnit === 'acre') {
    $plotAreaHa = $plotArea * 0.404686; // 1 acre = 0.404686 hectares
}

// Convert NPK from mg/kg to kg/ha
$nitrogen_kg_ha = convertToKgPerHa($nitrogen_mg_kg);
$phosphorus_kg_ha = convertToKgPerHa($phosphorus_mg_kg);
$potassium_kg_ha = convertToKgPerHa($potassium_mg_kg);

// Get crop requirements
$cropRequirements = getCropRequirements($conn, $cropId, $stateId, $districtId);

// Get fertilizer data
$fertilizers = getFertilizerData($conn);

// Calculate fertilizer requirements
$ureaRequired = calculateFertilizer($nitrogen_kg_ha, $cropRequirements['required_n'], 46); // Urea has 46% N
$dapRequired = calculateFertilizer($phosphorus_kg_ha, $cropRequirements['required_p'], 46); // DAP has 46% P
$mopRequired = calculateFertilizer($potassium_kg_ha, $cropRequirements['required_k'], 60); // MOP has 60% K

// Calculate total fertilizer needed based on plot area
$totalUrea = $ureaRequired * $plotAreaHa;
$totalDAP = $dapRequired * $plotAreaHa;
$totalMOP = $mopRequired * $plotAreaHa;

// Calculate organic manure alternative (simplified)
$organicManure = 0;
if ($nitrogen_kg_ha < $cropRequirements['required_n'] || 
    $phosphorus_kg_ha < $cropRequirements['required_p'] || 
    $potassium_kg_ha < $cropRequirements['required_k']) {
    $organicManure = 2; // 2 tons/ha as a base recommendation
    if ($plotAreaHa > 1) {
        $organicManure = $organicManure * $plotAreaHa;
    }
}

// Get pH status and recommendation
$phStatus = getPHStatus($ph, $cropRequirements['min_ph'], $cropRequirements['max_ph']);
$phRecommendation = getPHRecommendation($ph, $cropRequirements['min_ph'], $cropRequirements['max_ph']);

// Get names for display
$cropName = getCropName($conn, $cropId);
$stateName = getStateName($conn, $stateId);
$districtName = getDistrictName($conn, $districtId);
?>

<!-- Page Header -->
<section class="bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-4 mb-md-0">
                <h1 class="fw-bold">Analysis Results</h1>
                <p class="text-muted mb-0">Your personalized soil analysis and fertilizer recommendations</p>
            </div>
            <div class="col-md-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-md-end mb-0">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="analyze.php" class="text-decoration-none">Analyze Soil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Results</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Results Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Summary Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="card-title" id="cropName"><?php echo $cropName; ?></h4>
                                <p class="text-muted mb-1" id="stateName">State: <?php echo $stateName; ?></p>
                                <p class="text-muted mb-1" id="districtName">District: <?php echo $districtName; ?></p>
                                <p class="text-muted" id="plotArea">Plot Area: <?php echo $plotArea . ' ' . $areaUnit . ' (' . number_format($plotAreaHa, 2) . ' hectares)'; ?></p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <p class="text-muted mb-1">Analysis Date: <?php echo date('F d, Y'); ?></p>
                                <button id="generatePdf" class="btn btn-pdf mt-2">
                                    <i class="fas fa-file-pdf me-2"></i> Download PDF Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- NPK Comparison Table -->
                <div class="card result-card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white py-3">
                        <h5 class="mb-0">NPK Comparison</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-comparison">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Measured (kg/ha)</th>
                                        <th>Required (kg/ha)</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Nitrogen (N)</td>
                                        <td><?php echo number_format($nitrogen_kg_ha, 2); ?></td>
                                        <td><?php echo number_format($cropRequirements['required_n'], 2); ?></td>
                                        <td>
                                            <?php if ($nitrogen_kg_ha < $cropRequirements['required_n']): ?>
                                                <span class="deficiency">Deficient (<?php echo number_format($cropRequirements['required_n'] - $nitrogen_kg_ha, 2); ?> kg/ha needed)</span>
                                            <?php elseif ($nitrogen_kg_ha > $cropRequirements['required_n'] * 1.2): ?>
                                                <span class="excess">Excess (<?php echo number_format($nitrogen_kg_ha - $cropRequirements['required_n'], 2); ?> kg/ha extra)</span>
                                            <?php else: ?>
                                                <span class="optimal">Optimal</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Phosphorus (P)</td>
                                        <td><?php echo number_format($phosphorus_kg_ha, 2); ?></td>
                                        <td><?php echo number_format($cropRequirements['required_p'], 2); ?></td>
                                        <td>
                                            <?php if ($phosphorus_kg_ha < $cropRequirements['required_p']): ?>
                                                <span class="deficiency">Deficient (<?php echo number_format($cropRequirements['required_p'] - $phosphorus_kg_ha, 2); ?> kg/ha needed)</span>
                                            <?php elseif ($phosphorus_kg_ha > $cropRequirements['required_p'] * 1.2): ?>
                                                <span class="excess">Excess (<?php echo number_format($phosphorus_kg_ha - $cropRequirements['required_p'], 2); ?> kg/ha extra)</span>
                                            <?php else: ?>
                                                <span class="optimal">Optimal</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Potassium (K)</td>
                                        <td><?php echo number_format($potassium_kg_ha, 2); ?></td>
                                        <td><?php echo number_format($cropRequirements['required_k'], 2); ?></td>
                                        <td>
                                            <?php if ($potassium_kg_ha < $cropRequirements['required_k']): ?>
                                                <span class="deficiency">Deficient (<?php echo number_format($cropRequirements['required_k'] - $potassium_kg_ha, 2); ?> kg/ha needed)</span>
                                            <?php elseif ($potassium_kg_ha > $cropRequirements['required_k'] * 1.2): ?>
                                                <span class="excess">Excess (<?php echo number_format($potassium_kg_ha - $cropRequirements['required_k'], 2); ?> kg/ha extra)</span>
                                            <?php else: ?>
                                                <span class="optimal">Optimal</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>pH Value</td>
                                        <td><?php echo number_format($ph, 1); ?></td>
                                        <td><?php echo $cropRequirements['min_ph'] . ' - ' . $cropRequirements['max_ph']; ?></td>
                                        <td>
                                            <?php if ($phStatus === 'Acidic'): ?>
                                                <span class="deficiency">Acidic (pH too low)</span>
                                            <?php elseif ($phStatus === 'Alkaline'): ?>
                                                <span class="excess">Alkaline (pH too high)</span>
                                            <?php else: ?>
                                                <span class="optimal">Optimal</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Fertilizer Recommendations -->
                <div class="card result-card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white py-3">
                        <h5 class="mb-0">Fertilizer Recommendations</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <?php if ($ureaRequired > 0): ?>
                            <div class="col-md-6">
                                <div class="card fertilizer-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Urea (Nitrogen)</h5>
                                        <p class="card-text">Apply <?php echo number_format($ureaRequired, 2); ?> kg/ha</p>
                                        <p class="card-text fw-bold">Total for your plot: <?php echo number_format($totalUrea, 2); ?> kg</p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($dapRequired > 0): ?>
                            <div class="col-md-6">
                                <div class="card fertilizer-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">DAP (Phosphorus)</h5>
                                        <p class="card-text">Apply <?php echo number_format($dapRequired, 2); ?> kg/ha</p>
                                        <p class="card-text fw-bold">Total for your plot: <?php echo number_format($totalDAP, 2); ?> kg</p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($mopRequired > 0): ?>
                            <div class="col-md-6">
                                <div class="card fertilizer-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">MOP (Potassium)</h5>
                                        <p class="card-text">Apply <?php echo number_format($mopRequired, 2); ?> kg/ha</p>
                                        <p class="card-text fw-bold">Total for your plot: <?php echo number_format($totalMOP, 2); ?> kg</p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($organicManure > 0): ?>
                            <div class="col-md-6">
                                <div class="card fertilizer-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">Organic Alternative</h5>
                                        <p class="card-text">Apply <?php echo number_format($organicManure, 2); ?> tons of Vermicompost</p>
                                        <p class="card-text text-muted">Organic manure improves soil structure and provides slow-release nutrients.</p>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- pH Recommendation -->
                            <div class="col-md-12">
                                <div class="alert <?php echo $phStatus === 'Optimal' ? 'alert-success' : 'alert-warning'; ?> mb-0">
                                    <h5>pH Recommendation</h5>
                                    <p id="phRecommendation" class="mb-0"><?php echo $phRecommendation; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Application Tips -->
                <div class="card result-card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white py-3">
                        <h5 class="mb-0">Application Tips</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 text-success">
                                        <i class="fas fa-info-circle fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5>Timing</h5>
                                        <p class="text-muted">Apply fertilizers at the right growth stage. For <?php echo $cropName; ?>, the best time is typically before sowing and during the vegetative growth stage.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 text-success">
                                        <i class="fas fa-info-circle fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5>Method</h5>
                                        <p class="text-muted">For best results, incorporate fertilizers into the soil rather than surface application to reduce nutrient loss.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 text-success">
                                        <i class="fas fa-info-circle fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5>Split Application</h5>
                                        <p class="text-muted">Consider splitting the fertilizer application into 2-3 doses throughout the growing season for better nutrient utilization.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 text-success">
                                        <i class="fas fa-info-circle fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5>Safety</h5>
                                        <p class="text-muted">Always wear protective gear when handling fertilizers and follow the manufacturer's safety guidelines.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Fertilizer Store Links -->
                <div class="card result-card border-0 shadow-sm">
                    <div class="card-header bg-success text-white py-3">
                        <h5 class="mb-0">Where to Buy Fertilizers</h5>
                    </div>
                    <div class="card-body p-4 text-center">
                        <p class="mb-4">Purchase quality fertilizers from trusted suppliers:</p>
                        
                        <div class="store-links">
                            <a href="https://www.amazon.in/Fertilizers/b?node=4286640031" target="_blank" class="store-link text-decoration-none">
                                <div class="store-icon">
                                    <i class="fab fa-amazon"></i>
                                </div>
                                <div>Amazon</div>
                            </a>
                            
                            <a href="https://www.bigbasket.com/pc/kitchen-garden-pets/gardening/fertilizers-soil-enhancers/" target="_blank" class="store-link text-decoration-none">
                                <div class="store-icon">
                                    <i class="fas fa-shopping-basket"></i>
                                </div>
                                <div>BigBasket</div>
                            </a>
                            
                            <a href="https://www.indiamart.com/search.html?ss=fertilizers" target="_blank" class="store-link text-decoration-none">
                                <div class="store-icon">
                                    <i class="fas fa-store"></i>
                                </div>
                                <div>IndiaMart</div>
                            </a>
                            
                            <a href="https://www.flipkart.com/search?q=fertilizers" target="_blank" class="store-link text-decoration-none">
                                <div class="store-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div>Flipkart</div>
                            </a>
                        </div>
                        
                        <p class="mt-4 text-muted">You can also visit your local agricultural supply store or government-approved fertilizer distributors.</p>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="text-center mt-4">
                    <a href="analyze.php" class="btn btn-outline-success me-2">
                        <i class="fas fa-redo me-2"></i> New Analysis
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-2"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>