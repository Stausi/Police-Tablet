<?php
include '../header.php';

if($_SESSION["afdeling"] == "Advokatledelse" || $_SESSION["afdeling"] == "Dommer") {
    header("location: /police/pages/employed.php");
    exit;
}


// Check if the user is an admin or relevant role
$isWebsiteAdmin = $_SESSION["websiteadmin"] ?? false;



// Query to get all charges descriptions from population_cases
$casesSql = "SELECT sigtet FROM population_cases";
$casesResult = $link->query($casesSql);

// Beregn det totale antal sigtelser
$totalCasesSql = "SELECT COUNT(*) AS total_count FROM population_cases";
$totalCasesResult = $link->query($totalCasesSql);
$totalCasesRow = $totalCasesResult->fetch_assoc();
$totalSigtelser = $totalCasesRow['total_count'];

$sigtelserCounts = [];
while ($case = $casesResult->fetch_assoc()) {
    $sigtelser = explode(" - ", $case['sigtet']); // Splitting on ' - ' 
    foreach ($sigtelser as $sigtelse) {
        if (empty($sigtelse))
            continue;
        if (!isset($sigtelserCounts[$sigtelse])) {
            $sigtelserCounts[$sigtelse] = 0;
        }
        $sigtelserCounts[$sigtelse]++;
    }
}

arsort($sigtelserCounts); // Sort by occurrence

// Limit to top 10 most common sigtelser
$topSigtelser = array_slice($sigtelserCounts, 0, 10, true);


$categoryDistributionSql = "
SELECT p.ticketemne, t.sigtelse, (
    SELECT COUNT(*)
    FROM population_cases pc
    WHERE pc.sigtet LIKE CONCAT('%', t.sigtelse, '%')
) as total_count
FROM punishment p
JOIN tickets t ON p.ticketemne = t.emne
GROUP BY p.ticketemne, t.sigtelse
ORDER BY p.ticketemne, total_count DESC";

$categoryDistributionResult = $link->query($categoryDistributionSql);

$categoryDistribution = [];
while ($distribution = $categoryDistributionResult->fetch_assoc()) {
    $categoryDistribution[$distribution['ticketemne']][] = $distribution;
}


$topSigtelserPerEmneSql = "
SELECT p.ticketemne, t.sigtelse, COUNT(*) as total_count
FROM punishment p
JOIN tickets t ON p.ticketemne = t.emne
JOIN population_cases pc ON pc.sigtet LIKE CONCAT('%', t.sigtelse, '%')
GROUP BY p.ticketemne, t.sigtelse
ORDER BY p.ticketemne, total_count DESC";

$topSigtelserPerEmneResult = $link->query($topSigtelserPerEmneSql);

$topSigtelserPerEmne = [];
while ($row = $topSigtelserPerEmneResult->fetch_assoc()) {
    if (!isset($topSigtelserPerEmne[$row['ticketemne']])) {
        $topSigtelserPerEmne[$row['ticketemne']] = [];
    }
    if (count($topSigtelserPerEmne[$row['ticketemne']]) < 3) { // Limit til op til 3 sigtelser per emne
        $topSigtelserPerEmne[$row['ticketemne']][] = $row;
    }
}
?>


<main class="statistics-container">
    <div class="daily-header" id="dailyreport">
        <div class="daily-text">
            <h2>Statistikker</h2>
        </div>
        <p>Her kan du se statistikker over alle sigtelser og fordelingen af sigtelser efter kategori.</p>
        <p>Der er sammenlagt <strong> <?= $totalSigtelser ?> </strong> sigtelser i databasen.</p>
        <?php if ($_SESSION['username'] == "47"): // Kontrollerer om brugernavnet er "47" ?>
            <p> jeg når ikke at se den creed film... </p>
        <?php endif; ?>
    </div>
    <div class="statistics-section">
        <section class="common-cases">
            <h2 class="section-header">Mest almindelige sigtelser</h2>
            <table class="statistics-table">
                <thead>
                    <tr class="table-header">
                        <th class="header-item">Sigtelse</th>
                        <th class="header-item amount">Antal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topSigtelser as $sigtelse => $count): ?>
                        <?php if ($sigtelse === "Markedsføringsloven") continue; // Ignorer "Markedsføringsloven" ?>
                        <tr class="table-row">
                            <td class="row-item"><?= htmlspecialchars($sigtelse); ?></td>
                            <td class="row-item amount"><?= $count; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <section class="category-distribution">
            <h2 class="section-header">Fordeling af sigtelser efter kategori</h2>
            <?php foreach ($categoryDistribution as $ticketemne => $distributions): ?>
                <?php if ($ticketemne === "Markedsføringsloven") continue; // Ignorer kategorien "Markedsføringsloven" ?>
                <div class="category-section">
                    <h3 class="category-header"><?= htmlspecialchars($ticketemne); ?></h3>
                    <table class="statistics-table">
                        <thead>
                            <tr class="table-header">
                                <th class="header-item">Sigtelse</th>
                                <th class="header-item amount">Antal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($distributions as $distribution): ?>
                                <tr class="table-row">
                                    <td class="row-item"><?= htmlspecialchars($distribution['sigtelse']); ?></td>
                                    <td class="row-item amount"><?= $distribution['total_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- underkategori -->
                <div class="sub-category">
                    <h4 class="sub-category-header">Top 3 mest almindelige sigtelser i denne kategori</h4>
                    <table class="statistics-table">
                        <thead>
                            <tr class="table-header">
                                <th class="header-item">Sigtelse</th>
                                <th class="header-item amount">Antal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($topSigtelserPerEmne[$ticketemne])): ?>
                                <?php foreach ($topSigtelserPerEmne[$ticketemne] as $item): ?>
                                    <?php if ($item['sigtelse'] === "Markedsføringsloven") continue; // Ignorer markedsføringsloven, pga den fejlede og så var jeg sådan fuck it ?>
                                    <tr class="table-row">
                                        <td class="row-item"><?= htmlspecialchars($item['sigtelse']); ?></td>
                                        <td class="row-item amount"><?= $item['total_count']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php endforeach; ?>
        </section>
    </div>
</main>




<?php
include '../footer.php';
?>