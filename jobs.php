<?php
// jobs.php – Jobs page (owner: Nghia)
$page_title       = 'Job Vacancies';
$page_description = 'Current job vacancies at Harvest Power, a renewable energy company.';
$page_keywords    = 'Harvest Power, jobs, careers, renewable energy, engineer, content officer';
$page_author      = 'Tran Tan Nghia, COS10026 Group 03';
$current_page     = 'jobs';
$page_style       = <<<CSS
    .jobs-header-title {
        color: #1b5e20;
        border-bottom: 2px solid #1b5e20;
        padding-bottom: 0.3em;
    }
    .jobs-search-form {
        display: flex;
        gap: 0.5rem;
        margin: 1.5rem 0;
        flex-wrap: wrap;
    }
    .jobs-search-input {
        flex: 1;
        min-width: 220px;
        padding: 0.6rem 0.8rem;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 1rem;
    }
    .jobs-search-btn {
        background-color: #1b5e20;
        color: #ffffff;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1rem;
    }
    .jobs-search-btn:hover {
        background-color: #144718;
    }
    .jobs-reset-link {
        align-self: center;
        color: #1b5e20;
        text-decoration: underline;
        margin-left: 0.5rem;
    }
CSS;

include 'header.inc';
require_once 'settings.php'; // Ensure this matches your DB connection file

// 1. Connect to the database
$conn = @mysqli_connect($host, $user, $pwd, $sql_db);
$jobs = [];
$db_error = '';

// 2. Capture and sanitize search input
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (!$conn) {
    $db_error = "Database connection failed. Please try again later.";
} else {
    // 3. Query the database (filtered if a search term is provided)
    if ($search_query !== '') {
        $sql = "SELECT * FROM jobs 
                WHERE title LIKE ? 
                   OR job_ref LIKE ? 
                   OR description LIKE ? 
                   OR essential LIKE ? 
                   OR preferable LIKE ?";
        $stmt = mysqli_prepare($conn, $sql);
        $like_term = '%' . $search_query . '%';
        mysqli_stmt_bind_param($stmt, 'sssss', $like_term, $like_term, $like_term, $like_term, $like_term);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $sql = "SELECT * FROM jobs";
        $result = mysqli_query($conn, $sql);
    }

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $jobs[] = $row;
        }
        mysqli_free_result($result);
    } else {
        $db_error = "Unable to retrieve job listings at this time.";
    }
    mysqli_close($conn);
}
?>

  <main id="main-content" class="jobs-container">

    <h2>Inclusive Employment</h2>
    <p>Harvest Power welcomes applications from Aboriginal and Torres Strait Islander peoples. We work on Country, so we want our team to include the voices of its Traditional Custodians. Tell us in your application if you need any adjustments during the recruitment process.</p>
    
    <aside class="jobs-aside">
      <h2>Why Join Our Team?</h2>
      <p>We are a forward-thinking renewable energy group dedicated to professional ethics, digital privacy compliance, and sustainable carbon-reduction design choices.</p>
      <p>All candidates must submit matching reference codes through our standardized application workflow.</p>
    </aside>
    
    <!-- Primary Content Area -->
    <div class="jobs-main-content">
      <h1 class="jobs-header-title">Career Opportunities in Clean Energy</h1>
      <p>Join our mission to strengthen renewable technologies, manage smart grids, and deliver public engagement initiatives for clean energy solutions.</p>

      <!-- Search Bar Form -->
      <form method="get" action="jobs.php" class="jobs-search-form" role="search">
        <label for="job-search" class="visually-hidden" style="position:absolute; left:-9999px;">Search jobs</label>
        <input 
          type="text" 
          id="job-search" 
          name="q" 
          class="jobs-search-input"
          placeholder="Search by title, reference code, or keyword..." 
          value="<?php echo htmlspecialchars($search_query); ?>"
        >
        <button type="submit" class="jobs-search-btn">Search</button>
        <?php if ($search_query !== ''): ?>
          <a href="jobs.php" class="jobs-reset-link">Clear Search</a>
        <?php endif; ?>
      </form>

      <!-- Display Search Status -->
      <?php if ($search_query !== ''): ?>
        <p>Showing results for: <strong><?php echo htmlspecialchars($search_query); ?></strong> (<?php echo count($jobs); ?> found)</p>
      <?php endif; ?>

      <!-- Dynamic Job Listings -->
      <?php if ($db_error !== ''): ?>
        <p class="error-message"><?php echo htmlspecialchars($db_error); ?></p>
      <?php elseif (empty($jobs)): ?>
        <p>No job vacancies matched your search criteria. Try using different keywords or <a href="jobs.php">view all open roles</a>.</p>
      <?php else: ?>
        <?php foreach ($jobs as $job): ?>
          <section class="jobs-section">
            <h2><?php echo htmlspecialchars($job['title']); ?></h2>
            
            <p><strong>Job Reference Number:</strong> <code style="background-color: #f0f0f0; padding: 0.2em 0.4em; border-radius: 3px;"><?php echo htmlspecialchars($job['job_ref']); ?></code></p>
            
            <p><strong>Short Description:</strong> <?php echo htmlspecialchars($job['description']); ?></p>
            
            <h3>Role Position Details</h3>
            <ul>
              <li><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary']); ?></li>
              <li><strong>Reporting Line:</strong> <?php echo htmlspecialchars($job['reporting_line']); ?></li>
            </ul>

            <h3>Key Responsibilities</h3>
            <ul>
              <?php 
                // Splits newline-separated responsibilities into list items
                $responsibilities = explode("\n", trim($job['responsibilities']));
                foreach ($responsibilities as $item): 
                  if (trim($item) !== ''):
              ?>
                <li><?php echo htmlspecialchars(trim($item)); ?></li>
              <?php 
                  endif;
                endforeach; 
              ?>
            </ul>

            <h3>Position Requirements</h3>
            <ul>
              <li><strong>Essential:</strong> <?php echo htmlspecialchars($job['essential']); ?></li>
              <li><strong>Preferable:</strong> <?php echo htmlspecialchars($job['preferable']); ?></li>
            </ul>
          </section>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </main>

<?php include 'footer.inc'; ?>

