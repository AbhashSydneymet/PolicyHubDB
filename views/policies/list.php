<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Policy List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1>Policy List</h1>
        <form action="index.php" method="GET" class="mb-3">
            <input type="hidden" name="action" value="search">
            <div class="input-group">
                <input type="text" class="form-control" name="query" placeholder="Search policies by name..." value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>

        <!-- In list.php, add this after your search form -->
        <div class="mb-3">
            <form action="index.php" method="GET" id="filterForm">
                <input type="hidden" name="action" value="index">
                <div class="row">
                    <div class="col-md-3">
                        <label for="statusFilter" class="form-label">Status</label>
                        <select class="form-select" name="status" id="statusFilter">
                            <option value="">All Statuses</option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo $status['StatusID']; ?>"
                                    <?php echo (isset($_GET['status']) && $_GET['status'] == $status['StatusID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($status['StatusName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="officerFilter" class="form-label">Responsible Officer</label>
                        <select class="form-select" name="officer" id="officerFilter">
                            <option value="">All Officers</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?php echo $employee['EmployeeID']; ?>"
                                    <?php echo (isset($_GET['officer']) && $_GET['officer'] == $employee['EmployeeID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($employee['FirstName'] . ' ' . $employee['LastName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="versionFilter" class="form-label">Version</label>
                        <input type="number" step="0.01" class="form-control" name="version" id="versionFilter"
                            value="<?php echo isset($_GET['version']) ? htmlspecialchars($_GET['version']) : ''; ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-secondary">Apply Filters</button>
                        <a href="index.php?action=index" class="btn btn-outline-secondary ms-2">Clear Filters</a>
                    </div>
                </div>
            </form>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <table class="table">
            <thead>
                <tr>
                    <th>Policy Name</th>
                    <th>Version</th>
                    <th>Status</th>
                    <th>Responsible Officer</th>
                    <th>Policy Document</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($policies as $policy): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($policy['PolicyName']); ?></td>
                        <td><?php echo $policy['CurrentVersionNumber']; ?></td>
                        <td><?php echo htmlspecialchars($policy['StatusName']); ?></td>
                        <td><?php echo htmlspecialchars($policy['FirstName'] . ' ' . $policy['LastName']); ?></td>
                        <td>
                            <?php
                            $policyDocument = isset($policy['FilePath']) && $policy['FilePath'] ?
                                $policy['FilePath'] :
                                null;
                            ?>
                            <?php if ($policyDocument): ?>
                                <a href="<?php echo htmlspecialchars($policyDocument); ?>" target="_blank">View</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>

                        </td>
                        <td>
                            <a href="index.php?action=edit&id=<?php echo $policy['PolicyID']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <form action="index.php?action=delete" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this policy?');">
                                <input type="hidden" name="id" value="<?php echo $policy['PolicyID']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($policies)): ?>
            <div class="alert alert-info mt-3">No policies found matching your search criteria.</div>
        <?php endif; ?>

        <?php if (RoleManager::hasPermission('policy.create')): ?>
            <a href="index.php?action=create" class="btn btn-primary">Create New Policy</a>
        <?php endif; ?>
    </div>
</body>

</html>