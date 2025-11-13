<?php
/**
 * Content Moderator for Chat System
 * Detects inappropriate content using multiple methods
 */

class ContentModerator {
    private $profanity_words = [];
    private $warning_threshold = 2;
    private $block_threshold = 3;
    
    // Kolas.ai API Configuration
    private $kolas_client_id = '';
    private $kolas_client_secret = '';
    private $kolas_project_id = '';
    private $kolas_base_url = 'https://api.kolas.ai';
    private $access_token = null;
    
    public function __construct() {
        $this->loadProfanityList();
        $this->loadKolasConfig();
    }
    
    /**
     * Load Kolas.ai configuration
     */
    private function loadKolasConfig() {
        // Load from config file or environment variables
        $config_file = __DIR__ . '/../config/kolas_config.php';
        if (file_exists($config_file)) {
            $config = include $config_file;
            $this->kolas_client_id = $config['client_id'] ?? '';
            $this->kolas_client_secret = $config['client_secret'] ?? '';
            $this->kolas_project_id = $config['project_id'] ?? '';
        }
    }
    
    /**
     * Load profanity word list
     */
    private function loadProfanityList() {
        // Common inappropriate words (you can expand this list)
        $this->profanity_words = [
            // Explicit words (censored for safety)
            'damn', 'hell', 'crap', 'stupid', 'idiot', 'moron',
            'hate', 'kill', 'die', 'death', 'murder', 'suicide',
            'fuck', 'shit', 'bitch', 'asshole', 'bastard',
            'sex', 'porn', 'nude', 'naked', 'rape',
            'drug', 'cocaine', 'heroin', 'marijuana',
            'terrorist', 'bomb', 'weapon', 'gun', 'knife',
            'nigger', 'nigga', 'faggot', 'retard', 'whore',
            'slut', 'cunt', 'dick', 'pussy', 'cock',
            'commit suicide', 'kill yourself', 'end your life',
            'self harm', 'cut yourself', 'hang yourself',
            // Add more as needed
        ];
    }
    
    /**
     * Check message for inappropriate content
     * @param string $message
     * @return array
     */
    public function checkContent($message) {
        $result = [
            'is_appropriate' => true,
            'severity' => 'none',
            'flagged_words' => [],
            'suggestions' => [],
            'action' => 'allow',
            'ai_detection' => null
        ];
        
        // First, try AI detection with Kolas.ai (if available)
        $ai_result = $this->checkWithKolasAI($message);
        if ($ai_result) {
            $result['ai_detection'] = $ai_result;
            
            // Use AI result as primary detection
            if ($ai_result['category'] !== 'Neutral') {
                $result['is_appropriate'] = false;
                $result['severity'] = $this->mapAICategoryToSeverity($ai_result['category']);
                $result['action'] = $this->getActionFromSeverity($result['severity']);
                $result['suggestions'][] = 'Your message contains inappropriate content';
                $result['severity_score'] = $ai_result['probability'] * 10; // Convert to 0-10 scale
                return $result;
            }
        }
        
        // Fallback to local detection if AI is not available
        $message_lower = strtolower($message);
        $flagged_words = [];
        $severity_score = 0;
        
        // Check against profanity list
        foreach ($this->profanity_words as $word) {
            if (strpos($message_lower, $word) !== false) {
                $flagged_words[] = $word;
                $severity_score += $this->getWordSeverity($word);
            }
        }
        
        // Check for excessive caps (shouting)
        $caps_ratio = $this->getCapsRatio($message);
        if ($caps_ratio > 0.7) {
            $severity_score += 1;
            $result['suggestions'][] = 'Please avoid using excessive capital letters';
        }
        
        // Check for repeated characters
        if ($this->hasRepeatedChars($message)) {
            $severity_score += 1;
            $result['suggestions'][] = 'Please avoid repeating characters excessively';
        }
        
        // Check for spam patterns
        if ($this->isSpam($message)) {
            $severity_score += 2;
            $result['suggestions'][] = 'Please avoid spam-like messages';
        }
        
        // Determine action based on severity
        if ($severity_score >= $this->block_threshold) {
            $result['is_appropriate'] = false;
            $result['severity'] = 'high';
            $result['action'] = 'block';
        } elseif ($severity_score >= $this->warning_threshold) {
            $result['is_appropriate'] = false;
            $result['severity'] = 'medium';
            $result['action'] = 'warn';
        } elseif ($severity_score > 0) {
            $result['severity'] = 'low';
            $result['action'] = 'monitor';
        }
        
        $result['flagged_words'] = $flagged_words;
        $result['severity_score'] = $severity_score;
        
        return $result;
    }
    
    /**
     * Get severity score for a word
     */
    private function getWordSeverity($word) {
        $high_severity = [
            'fuck', 'shit', 'bitch', 'asshole', 'bastard', 'kill', 'die', 'murder', 'rape',
            'nigger', 'nigga', 'faggot', 'retard', 'whore', 'slut', 'cunt', 'dick', 'pussy', 'cock',
            'commit suicide', 'kill yourself', 'end your life', 'self harm', 'cut yourself', 'hang yourself',
            'suicide', 'terrorist', 'bomb', 'weapon'
        ];
        $medium_severity = ['damn', 'hell', 'crap', 'stupid', 'idiot', 'moron', 'hate', 'death'];
        
        if (in_array($word, $high_severity)) return 4;
        if (in_array($word, $medium_severity)) return 2;
        return 1;
    }
    
    /**
     * Check caps ratio
     */
    private function getCapsRatio($message) {
        $total_chars = strlen(preg_replace('/[^a-zA-Z]/', '', $message));
        if ($total_chars === 0) return 0;
        
        $caps_chars = strlen(preg_replace('/[^A-Z]/', '', $message));
        return $caps_chars / $total_chars;
    }
    
    /**
     * Check for repeated characters
     */
    private function hasRepeatedChars($message) {
        return preg_match('/(.)\1{4,}/', $message);
    }
    
    /**
     * Check for spam patterns
     */
    private function isSpam($message) {
        // Check for excessive repetition of words
        $words = explode(' ', strtolower($message));
        $word_counts = array_count_values($words);
        
        foreach ($word_counts as $count) {
            if ($count > 3) return true;
        }
        
        // Check for excessive punctuation
        $punct_count = preg_match_all('/[!?]{3,}/', $message);
        if ($punct_count > 0) return true;
        
        return false;
    }
    
    /**
     * Check content with Kolas.ai API
     */
    public function checkWithKolasAI($message) {
        if (empty($this->kolas_client_id) || empty($this->kolas_client_secret) || empty($this->kolas_project_id)) {
            return null; // Kolas.ai not configured
        }
        
        try {
            // Get access token if not already available
            if (!$this->access_token) {
                $this->access_token = $this->getKolasAccessToken();
                if (!$this->access_token) {
                    return null;
                }
            }
            
            // Send message for prediction
            $url = $this->kolas_base_url . '/predictions/predict';
            $data = [
                'projectId' => $this->kolas_project_id,
                'messages' => [
                    ['message' => $message]
                ]
            ];
            
            $headers = [
                'Authorization: Bearer ' . $this->access_token,
                'Content-Type: application/json'
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200 && $response) {
                $result = json_decode($response, true);
                if (isset($result['predictions'][0])) {
                    return $result['predictions'][0];
                }
            }
            
        } catch (Exception $e) {
            error_log('Kolas.ai API error: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Get access token from Kolas.ai
     */
    private function getKolasAccessToken() {
        $url = $this->kolas_base_url . '/oauth/token';
        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->kolas_client_id,
            'client_secret' => $this->kolas_client_secret
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 && $response) {
            $result = json_decode($response, true);
            return $result['access_token'] ?? null;
        }
        
        return null;
    }
    
    /**
     * Map AI category to severity level
     */
    private function mapAICategoryToSeverity($category) {
        $mapping = [
            'Insult' => 'high',
            'Spam' => 'medium',
            'Toxic' => 'high',
            'Harassment' => 'high',
            'Threat' => 'high',
            'Hate' => 'high',
            'Neutral' => 'none'
        ];
        
        return $mapping[$category] ?? 'medium';
    }
    
    /**
     * Get action from severity level
     */
    private function getActionFromSeverity($severity) {
        $mapping = [
            'high' => 'block',
            'medium' => 'warn',
            'low' => 'monitor',
            'none' => 'allow'
        ];
        
        return $mapping[$severity] ?? 'monitor';
    }
    
    /**
     * Log inappropriate content to database
     */
    public function logInappropriateContent($customer_id, $message, $result, $session_id = null) {
        try {
            // Include database functions
            require_once __DIR__ . '/../config/database.php';
            
            $sql = "INSERT INTO chat_moderation_logs 
                    (customer_id, session_id, message, severity, flagged_words, severity_score, action_taken, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $customer_id,
                $session_id,
                $message,
                $result['severity'],
                implode(', ', $result['flagged_words'] ?? []),
                $result['severity_score'] ?? 0,
                $result['action']
            ];
            
            executeQuery($sql, $params);
            
            // Also log to error log for debugging
            error_log('Inappropriate content detected: ' . json_encode([
                'customer_id' => $customer_id,
                'message' => $message,
                'severity' => $result['severity'],
                'action' => $result['action'],
                'ai_detection' => $result['ai_detection'] ?? null
            ]));
            
        } catch (Exception $e) {
            error_log('Failed to log inappropriate content: ' . $e->getMessage());
        }
    }
}
?>
