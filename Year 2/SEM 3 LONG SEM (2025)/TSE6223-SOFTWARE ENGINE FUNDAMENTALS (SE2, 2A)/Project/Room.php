<?php
/**
 * Room Model
 * 
 * Handles room-related operations
 */
class Room {
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        require_once __DIR__ . '/Database.php';
        $this->db = Database::getInstance();
    }
    
    /**
     * Get room by ID
     * 
     * @param int $id Room ID
     * @return array|false Room data
     */
    public function getById($id) {
        return $this->db->fetchOne(
            "SELECT r.*, c.name as category_name 
             FROM rooms r
             LEFT JOIN room_categories c ON r.category_id = c.id
             WHERE r.id = ?", 
            [$id]
        );
    }
    
    /**
     * Get all rooms
     * 
     * @param array $filters Optional filters
     * @return array Rooms
     */
    public function getAll($filters = []) {
        $sql = "SELECT r.*, c.name as category_name 
                FROM rooms r
                LEFT JOIN room_categories c ON r.category_id = c.id";
        $params = [];
        $where = [];
        
        // Apply filters
        if (!empty($filters['category_id'])) {
            $where[] = "r.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['status'])) {
            $where[] = "r.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['type'])) {
            $where[] = "r.type LIKE ?";
            $params[] = '%' . $filters['type'] . '%';
        }
        
        if (!empty($filters['capacity_min'])) {
            $where[] = "r.capacity >= ?";
            $params[] = $filters['capacity_min'];
        }
        
        if (!empty($filters['capacity_max'])) {
            $where[] = "r.capacity <= ?";
            $params[] = $filters['capacity_max'];
        }
        
        // Add WHERE clause if filters exist
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        // Add ordering
        $sql .= " ORDER BY r.type, r.room_number";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get available rooms for a date range
     * 
     * @param string $checkIn Check-in date (Y-m-d)
     * @param string $checkOut Check-out date (Y-m-d)
     * @param array $filters Optional filters
     * @return array Available rooms
     */
    public function getAvailableRooms($checkIn, $checkOut, $filters = []) {
        $sql = "SELECT r.*, c.name as category_name 
                FROM rooms r
                LEFT JOIN room_categories c ON r.category_id = c.id
                WHERE r.id NOT IN (
                    SELECT b.room_id 
                    FROM bookings b 
                    WHERE b.status IN ('approved', 'pending') 
                    AND (
                        (b.check_in_date <= ? AND b.check_out_date > ?) OR
                        (b.check_in_date < ? AND b.check_out_date >= ?) OR
                        (b.check_in_date >= ? AND b.check_out_date <= ?)
                    )
                )
                AND r.status = 'available'";
        
        $params = [
            $checkOut, $checkIn,  // First condition
            $checkOut, $checkOut, // Second condition
            $checkIn, $checkOut   // Third condition
        ];
        
        // Apply additional filters
        if (!empty($filters['category_id'])) {
            $sql .= " AND r.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['type'])) {
            $sql .= " AND r.type LIKE ?";
            $params[] = '%' . $filters['type'] . '%';
        }
        
        if (!empty($filters['capacity_min'])) {
            $sql .= " AND r.capacity >= ?";
            $params[] = $filters['capacity_min'];
        }
        
        // Add ordering
        $sql .= " ORDER BY r.price_per_day, r.capacity";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Add a new room
     * 
     * @param array $data Room data
     * @return int|bool Room ID or false on failure
     */
    public function add($data) {
        try {
            return $this->db->insert('rooms', $data);
        } catch (Exception $e) {
            error_log('Add room error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update a room
     * 
     * @param int $id Room ID
     * @param array $data Room data
     * @return bool Success status
     */
    public function update($id, $data) {
        try {
            $this->db->update('rooms', $data, 'id = ?', [$id]);
            return true;
        } catch (Exception $e) {
            error_log('Update room error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update room status
     * 
     * @param int $id Room ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updateStatus($id, $status) {
        try {
            $this->db->update('rooms', 
                ['status' => $status],
                'id = ?', 
                [$id]
            );
            return true;
        } catch (Exception $e) {
            error_log('Update room status error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a room
     * 
     * @param int $id Room ID
     * @return bool Success status
     */
    public function delete($id) {
        try {
            // Check if room has any bookings
            $bookings = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM bookings WHERE room_id = ?", 
                [$id]
            );
            
            if ($bookings['count'] > 0) {
                return false; // Room has bookings, cannot delete
            }
            
            $this->db->delete('rooms', 'id = ?', [$id]);
            return true;
        } catch (Exception $e) {
            error_log('Delete room error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get room categories
     * 
     * @return array Categories
     */
    public function getCategories() {
        return $this->db->fetchAll(
            "SELECT * FROM room_categories ORDER BY name"
        );
    }
    
    /**
     * Get room amenities
     * 
     * @param int $roomId Room ID
     * @return array Amenities
     */
    public function getRoomAmenities($roomId) {
        return $this->db->fetchAll(
            "SELECT a.* 
             FROM room_amenities a
             JOIN room_amenity_mapping m ON a.id = m.amenity_id
             WHERE m.room_id = ?
             ORDER BY a.name",
            [$roomId]
        );
    }
    
    /**
     * Update room amenities
     * 
     * @param int $roomId Room ID
     * @param array $amenityIds Amenity IDs
     * @return bool Success status
     */
    public function updateAmenities($roomId, $amenityIds) {
        try {
            $this->db->beginTransaction();
            
            // Delete existing mappings
            $this->db->delete('room_amenity_mapping', 'room_id = ?', [$roomId]);
            
            // Add new mappings
            foreach ($amenityIds as $amenityId) {
                $this->db->insert('room_amenity_mapping', [
                    'room_id' => $roomId,
                    'amenity_id' => $amenityId
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Update amenities error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Upload room image
     * 
     * @param int $roomId Room ID
     * @param array $file Uploaded file data
     * @return bool|string Success status or error message
     */
    public function uploadImage($roomId, $file) {
        // Check if file is valid
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return 'Invalid file upload';
        }
        
        // Check file size
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            return 'File size exceeds limit';
        }
        
        // Check file type
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            return 'File type not allowed';
        }
        
        // Generate unique filename
        $filename = 'room_' . $roomId . '_' . time() . '.' . $extension;
        $uploadPath = UPLOAD_DIR . 'rooms/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath . $filename)) {
            return 'Failed to save file';
        }
        
        // Update room
        try {
            $this->db->update('rooms', 
                ['image' => 'uploads/rooms/' . $filename],
                'id = ?', 
                [$roomId]
            );
            return true;
        } catch (Exception $e) {
            error_log('Upload room image error: ' . $e->getMessage());
            return 'System error occurred';
        }
    }
}
