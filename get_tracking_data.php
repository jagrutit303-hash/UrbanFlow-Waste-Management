<?php
include('config.php');

header('Content-Type: application/json');

$data = [
    'dispatches' => [],
    'drivers' => [],
    'dumps' => []
];

// Active dispatches with zone coordinates
$q1 = "SELECT f.assignment_id, f.vehicle_no, f.collector_id,
              COALESCE(r.request_id, CONCAT('D', d.dump_id)) as request_id,
              COALESCE(r.category, '🚨 Illegal Dump') as category,
              COALESCE(r.status, d.status) as status,
              COALESCE(r.comment, d.description) as comment,
              COALESCE(r.lat, d.citizen_lat, z.lat) as req_lat,
              COALESCE(r.lng, d.citizen_lng, z.lng) as req_lng,
              z.zone_name, z.lat as zone_lat, z.lng as zone_lng,
              u.full_name as citizen_name,
              dl.lat as driver_lat, dl.lng as driver_lng
       FROM fleet_assignments f
       LEFT JOIN disposal_requests r ON f.request_id = r.request_id
       LEFT JOIN illegal_dumps d ON f.dump_id = d.dump_id
       JOIN zones z ON (r.zone_id = z.zone_id OR d.zone_id = z.zone_id)
       JOIN users u ON (r.citizen_id = u.user_id OR d.citizen_id = u.user_id)
       LEFT JOIN driver_locations dl ON f.collector_id = dl.driver_id
       WHERE r.status = 'Dispatched' OR d.status = 'Cleanup Dispatched'";
$result = mysqli_query($conn, $q1);
while ($row = mysqli_fetch_assoc($result)) {
    $data['dispatches'][] = $row;
}

// All active driver locations
$q2 = "SELECT dl.*, u.full_name as driver_name 
       FROM driver_locations dl 
       JOIN users u ON dl.driver_id = u.user_id";
$result2 = mysqli_query($conn, $q2);
while ($row = mysqli_fetch_assoc($result2)) {
    $data['drivers'][] = $row;
}

// Active illegal dump reports (not resolved)
$q3 = "SELECT d.*, z.zone_name, z.lat as zone_lat, z.lng as zone_lng, u.full_name as citizen_name
       FROM illegal_dumps d
       JOIN zones z ON d.zone_id = z.zone_id
       JOIN users u ON d.citizen_id = u.user_id
       WHERE d.status != 'Resolved'";
$result3 = mysqli_query($conn, $q3);
while ($row = mysqli_fetch_assoc($result3)) {
    $data['dumps'][] = $row;
}

echo json_encode($data);
?>
