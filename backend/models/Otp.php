<?php

class Otp {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Generates a new 6-digit OTP for the given email, invalidates any
     * previous unused OTPs for that email, stores the new one, and
     * returns the plain OTP string so the caller can email it.
     *
     * expires_at is calculated by MySQL itself (NOW() + 10 minutes), not
     * by PHP — this avoids any mismatch between PHP's and MySQL's clocks
     * or timezone settings, which would otherwise make codes look expired
     * immediately.
     */
    public function generate(string $email): string {
        // Invalidate any old unused OTPs for this email first
        $stmt = $this->db->prepare("UPDATE otp_codes SET is_used = 1 WHERE email = ? AND is_used = 0");
        $stmt->execute([$email]);

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $stmt = $this->db->prepare(
            "INSERT INTO otp_codes (email, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))"
        );
        $stmt->execute([$email, $otp]);

        return $otp;
    }

    /**
     * Checks whether the given OTP is valid (correct, unused, not expired)
     * for the given email. If valid, marks it as used and returns true.
     */
    public function verify(string $email, string $otp): bool {
        $stmt = $this->db->prepare(
            "SELECT id FROM otp_codes
             WHERE email = ? AND otp = ? AND is_used = 0 AND expires_at >= NOW()
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$email, $otp]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        $update = $this->db->prepare("UPDATE otp_codes SET is_used = 1 WHERE id = ?");
        $update->execute([$row['id']]);

        return true;
    }
}