<?php
class AddEpisodeHandler
{
    private $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    public function addEpisodes($movie_id, $episode_titles, $episode_urls, $episode_ids)
    {
        foreach ($episode_titles as $index => $title) {
            $url = $episode_urls[$index];
            $id = isset($episode_ids[$index]) ? (int)$episode_ids[$index] : 0;

            if ($id === 0 && !empty($title) && !empty($url)) {
                $stmt = $this->conn->prepare("INSERT INTO episodes (movie_id, episode_title, video_url) VALUES (?, ?, ?)");
                if ($stmt === false) {
                    die("Prepare failed: " . $this->conn->error);
                }
                $stmt->bind_param("iss", $movie_id, $title, $url);
                if (!$stmt->execute()) {
                    die("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            }
        }
    }
}
