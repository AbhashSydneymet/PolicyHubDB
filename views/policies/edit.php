<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Policy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Edit Policy</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="index.php?action=update&id=<?php echo $policy['PolicyID']; ?>" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="PolicyName" class="form-label">Policy Name</label>
                <input type="text" class="form-control" id="PolicyName" name="PolicyName" value="<?php echo htmlspecialchars($policy['PolicyName']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="CurrentVersionNumber" class="form-label">Version Number</label>
                <input type="number" step="0.01" class="form-control" id="CurrentVersionNumber" name="CurrentVersionNumber" value="<?php echo $policy['CurrentVersionNumber']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="StatusID" class="form-label">Status</label>
                <select class="form-select" id="StatusID" name="StatusID" required>
                    <option value="">Select Status</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo $status['StatusID']; ?>" <?php echo $status['StatusID'] == $policy['StatusID'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status['StatusName']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="ResponsibleOfficerID" class="form-label">Responsible Officer</label>
                <select class="form-select" id="ResponsibleOfficerID" name="ResponsibleOfficerID" required>
                    <option value="">Select Responsible Officer</option>
                    <?php foreach ($employees as $employee): ?>
                        <option value="<?php echo $employee['EmployeeID']; ?>" <?php echo $employee['EmployeeID'] == $policy['ResponsibleOfficerID'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($employee['FirstName'] . ' ' . $employee['LastName']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="ApprovingAuthorityID" class="form-label">Approving Authority</label>
                <select class="form-select" id="ApprovingAuthorityID" name="ApprovingAuthorityID" required>
                    <option value="">Select Approving Authority</option>
                    <?php foreach ($authorities as $authority): ?>
                        <option value="<?php echo $authority['AuthorityID']; ?>" <?php echo $authority['AuthorityID'] == $policy['ApprovingAuthorityID'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($authority['AuthorityName']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date fields -->
            <div class="mb-3">
                <label for="DateCreated" class="form-label">Date Created</label>
                <input type="date" class="form-control" id="DateCreated" name="DateCreated" value="<?php echo $policy['DateCreated']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="DateSubmittedToAuthority" class="form-label">Date Submitted to Authority</label>
                <input type="date" class="form-control" id="DateSubmittedToAuthority" name="DateSubmittedToAuthority" value="<?php echo $policy['DateSubmittedToAuthority']; ?>">
            </div>

            <div class="mb-3">
                <label for="DateApproved" class="form-label">Date Approved</label>
                <input type="date" class="form-control" id="DateApproved" name="DateApproved" value="<?php echo $policy['DateApproved']; ?>">
            </div>

            <div class="mb-3">
                <label for="NextReviewDate" class="form-label">Next Review Date</label>
                <input type="date" class="form-control" id="NextReviewDate" name="NextReviewDate" value="<?php echo $policy['NextReviewDate']; ?>">
            </div>

            <div class="mb-3">
            <label for="PolicyDocument" class="form-label">Policy Document</label>
            <?php if ($document): ?>
            <a href="<?php echo $document['DocumentPath']; ?>" target="_blank" class="form-control-plaintext">View Policy Document</a>
            <?php else: ?>
            <input type="file" class="form-control" id="policyDocument" name="PolicyDocument">
            <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Update Policy</button>
        </form>
    </div>
</body>
</html>
