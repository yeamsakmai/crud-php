<?php
class EpisodeManager
{
    private $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    // Get episodes
    public function getEpisodes($movie_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM episodes WHERE movie_id = ?");
        $stmt->bind_param("i", $movie_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Delete episode
    public function deleteEpisode($episode_id)
    {
        $stmt = $this->conn->prepare("DELETE FROM episodes WHERE id = ?");
        $stmt->bind_param("i", $episode_id);
        $stmt->execute();
    }

    // Update episode
    public function updateEpisodes($episode_ids, $episode_titles, $episode_urls)
    {
        foreach ($episode_ids as $index => $id) {
            $title = $episode_titles[$index];
            $url = $episode_urls[$index];

            if (!empty($id)) {
                $stmt = $this->conn->prepare("UPDATE episodes SET episode_title = ?, video_url = ? WHERE id = ?");
                $stmt->bind_param("ssi", $title, $url, $id);
                $stmt->execute();
            }
        }
    }
}
