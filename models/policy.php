<?php
// models/Policy.php
require_once 'config/database.php';

class Policy {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Searching for policies based on a query
    public function searchPolicies($query) {
        // Using prepared statement to prevent SQL injection
        $stmt = $this->db->connection->prepare(
            "SELECT p.*, 
                   e.FirstName, e.LastName, 
                   a.AuthorityName, 
                   s.StatusName,
                   d.FilePath
            FROM Policy p
            LEFT JOIN Employee e ON p.ResponsibleOfficerID = e.EmployeeID
            LEFT JOIN Authority a ON p.ApprovingAuthorityID = a.AuthorityID
            LEFT JOIN Status s ON p.StatusID = s.StatusID
            LEFT JOIN PolicyVersion pv ON p.PolicyID = pv.PolicyID
            LEFT JOIN Document d ON pv.DocumentID = d.DocumentID
            WHERE p.PolicyName LIKE ? 
            OR e.FirstName LIKE ? 
            OR e.LastName LIKE ? 
            OR a.AuthorityName LIKE ? 
            OR s.StatusName LIKE ?
            GROUP BY p.PolicyID" 
        );
    
        // Adding  wildcard for partial matching
        $searchTerm = '%' . $query . '%';
        $stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // To create a new policy
    public function createPolicy($data) {
        $stmt = $this->db->connection->prepare(
            "INSERT INTO Policy (
                PolicyName, CurrentVersionNumber, StatusID, 
                ResponsibleOfficerID, ApprovingAuthorityID, 
                DateCreated, DateSubmittedToAuthority, 
                DateApproved, NextReviewDate
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
    
        $stmt->bind_param(
            "sdiiissss", 
            $data['PolicyName'], 
            $data['CurrentVersionNumber'], 
            $data['StatusID'],
            $data['ResponsibleOfficerID'], 
            $data['ApprovingAuthorityID'], 
            $data['DateCreated'], 
            $data['DateSubmittedToAuthority'], 
            $data['DateApproved'], 
            $data['NextReviewDate']
        );
    
        return $stmt->execute() ? $this->db->connection->insert_id : false;
    }
    

    public function createDocument($documentPath) {
        $stmt = $this->db->connection->prepare(
            "INSERT INTO Document (DocumentName, FilePath, DateUploaded) 
            VALUES (?, ?, ?)"
        );
        $documentName = $_FILES['policyDocument']['name'];
        $dateUploaded = date('Y-m-d H:i:s');
        
        $stmt->bind_param("sss", $documentName, $documentPath, $dateUploaded);
        return $stmt->execute() ? $this->db->connection->insert_id : false;  // Returns DocumentID if successful
    }
    

    public function createPolicyVersion($policyID, $documentID) {
        $stmt = $this->db->connection->prepare(
            "INSERT INTO PolicyVersion (PolicyID, DocumentID, VersionCreateDate) 
            VALUES (?, ?, ?)"
        );
        $versionCreateDate = date('Y-m-d H:i:s'); // Current timestamp
        
        $stmt->bind_param("iis", $policyID, $documentID, $versionCreateDate);
        return $stmt->execute();
    }
    
    
    
    
    public function updatePolicyDocument($policyId, $documentPath) {
        $this->db->connection->begin_transaction();
        
        try {
            // Create new document record
            $stmt = $this->db->connection->prepare(
                "INSERT INTO Document (DocumentName, FilePath, DateUploaded) 
                VALUES (?, ?, NOW())"
            );
            $documentName = basename($documentPath);
            $stmt->bind_param("ss", $documentName, $documentPath);
            $stmt->execute();
            $documentId = $this->db->connection->insert_id;

            // Create new policy version
            $stmt = $this->db->connection->prepare(
                "INSERT INTO PolicyVersion (PolicyID, DocumentID, VersionCreateDate) 
                VALUES (?, ?, NOW())"
            );
            $stmt->bind_param("ii", $policyId, $documentId);
            $stmt->execute();

            $this->db->connection->commit();
            return true;
        } catch (Exception $e) {
            $this->db->connection->rollback();
            error_log("Error updating policy document: " . $e->getMessage());
            return false;
        }
    }
    // Retrieving all policies
    public function getAllPolicies() {
        $query = "
            SELECT p.*, 
                   e.FirstName, e.LastName, 
                   a.AuthorityName, 
                   s.StatusName, 
                   d.FilePath 
            FROM Policy p
            LEFT JOIN Employee e ON p.ResponsibleOfficerID = e.EmployeeID
            LEFT JOIN Authority a ON p.ApprovingAuthorityID = a.AuthorityID
            LEFT JOIN Status s ON p.StatusID = s.StatusID
            LEFT JOIN PolicyVersion pv ON p.PolicyID = pv.PolicyID
            LEFT JOIN Document d ON pv.DocumentID = d.DocumentID
            GROUP BY p.PolicyID"; // Ensures unique policies are fetched
        $result = $this->db->connection->query($query);
    
        if ($result === false) {
            error_log("SQL Error: " . $this->db->connection->error);
            echo "Database query error: " . $this->db->connection->error;
            return false;
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    

    // Retrieving a single policy by ID
    public function getPolicyById($id) {
        $stmt = $this->db->connection->prepare(
            "SELECT * FROM Policy WHERE PolicyID = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }

    // Updating an existing policy
    public function updatePolicy($id, $data) {
        $this->db->connection->begin_transaction();
        
        try {
            // Update basic policy information
            $stmt = $this->db->connection->prepare(
                "UPDATE Policy SET 
                    PolicyName = ?, 
                    CurrentVersionNumber = ?, 
                    StatusID = ?, 
                    ResponsibleOfficerID = ?, 
                    ApprovingAuthorityID = ?, 
                    DateCreated = ?, 
                    DateSubmittedToAuthority = ?, 
                    DateApproved = ?, 
                    NextReviewDate = ? 
                WHERE PolicyID = ?"
            );

            $stmt->bind_param(
                "sdiiissssi", 
                $data['PolicyName'], 
                $data['CurrentVersionNumber'], 
                $data['StatusID'],
                $data['ResponsibleOfficerID'], 
                $data['ApprovingAuthorityID'], 
                $data['DateCreated'], 
                $data['DateSubmittedToAuthority'], 
                $data['DateApproved'], 
                $data['NextReviewDate'],
                $id
            );

            $result = $stmt->execute();

            if (!$result) {
                throw new Exception("Failed to update policy information");
            }

            $this->db->connection->commit();
            return true;
        } catch (Exception $e) {
            $this->db->connection->rollback();
            error_log("Error updating policy: " . $e->getMessage());
            return false;
        }
    }


    // Deleting a policy by ID
    public function deletePolicy($id) {
            // Start transaction
            $this->db->connection->begin_transaction();
            
            try {
                // First, get all document paths associated with this policy
                $stmt = $this->db->connection->prepare(
                    "SELECT d.DocumentID, d.FilePath 
                     FROM Document d
                     JOIN PolicyVersion pv ON d.DocumentID = pv.DocumentID
                     WHERE pv.PolicyID = ?"
                );
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $documents = $result->fetch_all(MYSQLI_ASSOC);
    
                // Delete related records in PolicyVersion
                $stmt = $this->db->connection->prepare("DELETE FROM PolicyVersion WHERE PolicyID = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
    
                // Delete documents from Document table and filesystem
                foreach ($documents as $doc) {
                    // Delete physical file
                    if (file_exists($doc['FilePath'])) {
                        unlink($doc['FilePath']);
                    }
                    
                    // Delete from Document table
                    $stmt = $this->db->connection->prepare("DELETE FROM Document WHERE DocumentID = ?");
                    $stmt->bind_param("i", $doc['DocumentID']);
                    $stmt->execute();
                }
    
                // Finally, delete the policy
                $stmt = $this->db->connection->prepare("DELETE FROM Policy WHERE PolicyID = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
    
                // Commit transaction
                $this->db->connection->commit();
                return true;
            } catch (Exception $e) {
                // Rollback on error
                $this->db->connection->rollback();
                error_log("Error deleting policy: " . $e->getMessage());
                return false;
            }
    }


    // In Policy.php
public function getPoliciesWithFilters($filters) {
    $sql = "SELECT p.*, 
                   e.FirstName, e.LastName, 
                   a.AuthorityName, 
                   s.StatusName,
                   d.FilePath
            FROM Policy p
            LEFT JOIN Employee e ON p.ResponsibleOfficerID = e.EmployeeID
            LEFT JOIN Authority a ON p.ApprovingAuthorityID = a.AuthorityID
            LEFT JOIN Status s ON p.StatusID = s.StatusID
            LEFT JOIN PolicyVersion pv ON p.PolicyID = pv.PolicyID
            LEFT JOIN Document d ON pv.DocumentID = d.DocumentID
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    // Add filter conditions
    if (!empty($filters['status'])) {
        $sql .= " AND p.StatusID = ?";
        $params[] = $filters['status'];
        $types .= "i";
    }
    
    if (!empty($filters['officer'])) {
        $sql .= " AND p.ResponsibleOfficerID = ?";
        $params[] = $filters['officer'];
        $types .= "i";
    }
    
    if (!empty($filters['version'])) {
        $sql .= " AND p.CurrentVersionNumber = ?";
        $params[] = $filters['version'];
        $types .= "d";
    }
    
    $sql .= " GROUP BY p.PolicyID";
    
    $stmt = $this->db->connection->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}
}
?>
