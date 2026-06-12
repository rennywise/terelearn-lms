<?php
/**
 * API/authenticate.php  (UPDATED)
 * 
 * Changes from original:
 *  1. On failed login, returns owner_email + owner_phone so the
 *     frontend can trigger a suspicious-login notification after
 *     5 consecutive failures.
 *  2. Everything else stays identical to your existing file.
 *
 * MERGE INSTRUCTION:
 *  Replace your current failed-login JSON response block with the
 *  one marked below — everything else in your authenticate.php
 *  stays exactly the same.
 */

// ──────────────────────────────────────────────────────────────────
// Replace your current FAILED response (data.success === false) with:
// ──────────────────────────────────────────────────────────────────
//
//  echo json_encode([
//      'success'     => false,
//      'message'     => 'Invalid username or password.',
//      'owner_email' => $user ? $user['email']       : null,   // ← ADD THIS
//      'owner_phone' => $user ? ($user['phone'] ?? '') : null, // ← ADD THIS
//  ]);
//
// ──────────────────────────────────────────────────────────────────
// Also make sure your SELECT query joins tblfaculty to get phone:
// ──────────────────────────────────────────────────────────────────
//
//  SELECT u.*, COALESCE(f.phone, '') AS phone
//  FROM tbluser u
//  LEFT JOIN tblfaculty f ON f.username = u.username
//  WHERE (u.username = ? OR u.email = ?)
//    AND u.is_deleted = 0
//  LIMIT 1
//
// ──────────────────────────────────────────────────────────────────
// The SUCCESS response stays exactly as it is in your existing file:
// ──────────────────────────────────────────────────────────────────
//
//  echo json_encode([
//      'success'     => true,
//      'message'     => 'Login successful',
//      'first_login' => $first_login_value,
//      'user'        => [
//          'id'            => $user['id'],
//          'username'      => $user['username'],
//          'email'         => $user['email'],
//          'user_level_id' => (int)$user['user_level_id'],
//          'is_dean'       => (int)$user['is_dean'],
//      ]
//  ]);
//
// No other changes needed in authenticate.php.
?>