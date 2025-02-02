<?php
// controllers/PolicyController.php
require_once 'models/Policy.php';
require_once 'auth/AuthMiddleware.php';


class PolicyController
{
    private $policyModel;

    public function __construct()
    {
        $this->policyModel = new Policy();
    }

    // In PolicyController.php
    public function index()
    {
        // Get filter values
        $filters = [
            'status' => isset($_GET['status']) ? intval($_GET['status']) : null,
            'officer' => isset($_GET['officer']) ? intval($_GET['officer']) : null,
            'version' => isset($_GET['version']) ? floatval($_GET['version']) : null
        ];

        // Get statuses and employees for filter dropdowns
        $statuses = $this->getStatuses();
        $employees = $this->getEmployees();

        // Get filtered policies
        $policies = $this->policyModel->getPoliciesWithFilters($filters);

        include 'views/policies/list.php';
    }

    // controllers/PolicyController.php

    public function search()
    {
        $query = isset($_GET['query']) ? $_GET['query'] : '';

        // If there's no search query, just return all policies
        if (empty($query)) {
            $this->index(); // Call the index function to show all policies
            return;
        }

        // Use the model to fetch the search results
        $policies = $this->policyModel->searchPolicies($query);

        // Pass the results to the view
        include 'views/policies/list.php';
    }

    public function create()
    {
        // Fetch dropdown data for form
        $employees = $this->getEmployees();
        $authorities = $this->getAuthorities();
        $statuses = $this->getStatuses();

        include 'views/policies/create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize inputs
            $data = [
                'PolicyName' => filter_var($_POST['PolicyName'], FILTER_SANITIZE_STRING),
                'CurrentVersionNumber' => floatval($_POST['CurrentVersionNumber']),
                'StatusID' => intval($_POST['StatusID']),
                'ResponsibleOfficerID' => intval($_POST['ResponsibleOfficerID']),
                'ApprovingAuthorityID' => intval($_POST['ApprovingAuthorityID']),
                'DateCreated' => $_POST['DateCreated'],
                'DateSubmittedToAuthority' => $_POST['DateSubmittedToAuthority'] ?? null,
                'DateApproved' => $_POST['DateApproved'] ?? null,
                'NextReviewDate' => $_POST['NextReviewDate'] ?? null
            ];

            // Check if a file was uploaded
            $documentPath = null;
            if (isset($_FILES['policyDocument']) && $_FILES['policyDocument']['error'] == UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/'; // Dedicated upload directory

                // Create upload directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Generate a unique filename to prevent overwriting
                $fileName = uniqid() . '_' . basename($_FILES['policyDocument']['name']);
                $documentPath = $uploadDir . $fileName;

                // Move uploaded file
                if (move_uploaded_file($_FILES['policyDocument']['tmp_name'], $documentPath)) {
                    // File upload successful
                    $documentID = $this->policyModel->createDocument($documentPath);
                } else {
                    // File upload failed
                    $error = "Failed to upload document";
                    include 'views/policies/create.php';
                    return;
                }
            }

            // Create the policy
            $policyID = $this->policyModel->createPolicy($data);

            if ($policyID) {
                // If a document was uploaded, link it to the policy version
                if (isset($documentID) && $documentID) {
                    $this->policyModel->createPolicyVersion($policyID, $documentID);
                }

                header('Location: index.php?action=index');
                exit();
            } else {
                $error = "Failed to create policy";
                include 'views/policies/create.php';
            }
        }
    }

    public function delete()
    {
        $id = $_POST['id'];
        $result = $this->policyModel->deletePolicy($id);

        if ($result) {
            header('Location: index.php?action=index');
            exit();
        } else {
            echo '<pre>';
            var_dump($id);
            echo '</pre>';
            $error = "Failed to delete policy";
            $policies = $this->policyModel->getAllPolicies();
            $statuses = $this->getStatuses();
            $employees = $this->getEmployees();
            include 'views/policies/list.php';
        }
    }




    public function edit($id)
    {
        $policy = $this->policyModel->getPolicyById($id);
        $employees = $this->getEmployees();
        $authorities = $this->getAuthorities();
        $statuses = $this->getStatuses();

        include 'views/policies/edit.php';
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize inputs
            $data = [
                'PolicyName' => filter_var($_POST['PolicyName'], FILTER_SANITIZE_STRING),
                'CurrentVersionNumber' => floatval($_POST['CurrentVersionNumber']),
                'StatusID' => intval($_POST['StatusID']),
                'ResponsibleOfficerID' => intval($_POST['ResponsibleOfficerID']),
                'ApprovingAuthorityID' => intval($_POST['ApprovingAuthorityID']),
                'DateCreated' => $_POST['DateCreated'],
                'DateSubmittedToAuthority' => $_POST['DateSubmittedToAuthority'] ?? null,
                'DateApproved' => $_POST['DateApproved'] ?? null,
                'NextReviewDate' => $_POST['NextReviewDate'] ?? null
            ];

            // Update the policy record
            $result = $this->policyModel->updatePolicy($id, $data);

            if ($result) {
                // Handle document upload if new document is provided
                if (isset($_FILES['policyDocument']) && $_FILES['policyDocument']['error'] == 0) {
                    $uploadDir = 'uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $fileName = uniqid() . '_' . basename($_FILES['policyDocument']['name']);
                    $documentPath = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['policyDocument']['tmp_name'], $documentPath)) {
                        // Update policy document
                        $this->policyModel->updatePolicyDocument($id, $documentPath);
                    } else {
                        $error = "Failed to upload document";
                        include 'views/policies/edit.php';
                        return;
                    }
                }

                header('Location: index.php?action=index');
                exit();
            } else {
                $error = "Failed to update policy";
                include 'views/policies/edit.php';
            }
        }

    }



    // Helper methods to get dropdown data
    private function getEmployees()
    {
        $db = new Database();
        $result = $db->connection->query("SELECT EmployeeID, FirstName, LastName FROM Employee");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function getAuthorities()
    {
        $db = new Database();
        $result = $db->connection->query("SELECT AuthorityID, AuthorityName FROM Authority");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function getStatuses()
    {
        $db = new Database();
        $result = $db->connection->query("SELECT StatusID, StatusName FROM Status");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
