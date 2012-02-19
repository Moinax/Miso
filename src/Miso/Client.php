<?php

namespace Miso;

class Client extends \OAuth {
    const STATUS_NEED_REQUEST_TOKEN = 0;
    const STATUS_NEED_ACCESS_TOKEN = 1;
    const STATUS_CONNECTED = 2;

    const MISO_REQUEST_TOKEN_URL = 'http://www.gomiso.com/oauth/request_token';
    const MISO_ACCESS_TOKEN_URL = 'http://www.gomiso.com/oauth/access_token';
    const MISO_AUTHORIZE_URL = 'http://www.gomiso.com/oauth/authorize';

    /**
     * @var stdClass
     */
    private $user;

    /**
     * @var stdClass
     */
    private $favorites;

    /**
     * Get user from Api
     * @throws \Exception
     * @return stdClass
     */
    public function getUser() {
        $this->fetch('http://gomiso.com/api/oauth/v1/users/show.json');
        $this->user = json_decode($this->getLastResponse())->user;

        if(!$this->user) {
            throw new \Exception('User not found.');
        }

        return $this->user;
    }

    /**
     * Get favorites from Api
     *
     * @return array
     */
    public function getFavorites() {
        $this->fetch('http://gomiso.com/api/oauth/v1/media/favorites.json');
        $this->favorites = json_decode($this->getLastResponse());

        return $this->favorites;
    }

    /**
     * Get media info from Api
     *
     * @param int $media_id
     * @return array
     */
    public function getMediaInfo($media_id) {
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/episodes.json',
            array(
                 'media_id' => $media_id,
                 'count' => 0
            )
        );
       return json_decode($this->getLastResponse());
    }

    /**
     * Get media details from Api
     *
     * @param int $media_id
     * @return array
     */
    public function getMediaDetails($media_id) {
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/media/show.json',
            array(
                 'media_id' => $media_id,
            )
        );
       return json_decode($this->getLastResponse())->media;
    }

    /**
     * Get episodes from Api
     *
     * @param $media
     * @param $season
     * @return array
     */
    public function getEpisodesBySeason($media, $season) {
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/episodes.json',
            array(
                 'media_id' => $media->getId(),
                 'season_num' => $season,
            )
        );
        return json_decode($this->getLastResponse())->episodes;
    }

    /**
     * Get episode from Api
     * @param $media_id
     * @param $season_num
     * @param $episode_num
     * @return stdClass
     */
    public function getEpisode($media_id, $season_num, $episode_num) {
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/episodes/show.json',
            array(
                 'media_id' => $media_id,
                 'season_num' => $season_num,
                 'episode_num' => $episode_num,
            )
        );
        return json_decode($this->getLastResponse())->episode;

    }

    /**
     * Get all new episodes from the Api
     * @param $media_id
     * @param $count
     * @return array
     */
    public function getNewEpisodes($media_id, $count) {
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/episodes.json',
            array(
                 'media_id' => $media_id,
                 'count' => $count,
            )
        );
        return json_decode($this->getLastResponse())->episodes;

    }

    /**
     * Get checkins from Api
     *
     * @param $media
     * @param $user_id
     * @return array
     */
    public function getCheckins($media, $user_id) {
        $checkinsParams = array();
        $checkinsParams['media_id'] = $media->getId();
        $checkinsParams['user_id'] = $user_id;
        $checkinsParams['count'] = 50;
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/checkins.json',
            $checkinsParams
        );
        return $this->sanitizeCheckins(json_decode($this->getLastResponse()));
    }

    /**
     * Get new checkins from Api
     *
     * @param $last_checkin_id
     * @param $user_id
     * @return array
     */
    public function getNewCheckins($last_checkin_id, $user_id) {
        $checkinsParams = array();
        $checkinsParams['user_id'] = $user_id;
        $checkinsParams['since_id'] = $last_checkin_id;
        $checkinsParams['count'] = 50;
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/checkins.json',
            $checkinsParams
        );
        return $this->sanitizeCheckins(json_decode($this->getLastResponse()));

    }
    /**
     * Post a checking to Miso Api
     * @param int $media_id
     * @param int $season_num
     * @param int $episode_num
     * @return stdClass
     */
    public function addCheckin($media_id, $season_num, $episode_num)
    {
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/checkins.json',
            array(
                 'media_id' => $media_id,
                 'season_num' => $season_num,
                 'episode_num' => $episode_num,
            ),
            OAUTH_HTTP_METHOD_POST
        );
        return $this->sanitizeCheckins(json_decode($this->getLastResponse())->checkin);

    }

    /**
     * Search for Media through Miso API
     *
     * @param string $query
     * @return array
     */
    public function searchMedia($query) {
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/media.json',
            array(
                'q' => $query,
                'kind' => 'TvShow',
            ),
            OAUTH_HTTP_METHOD_POST
        );

        return json_decode($this->getLastResponse());
    }

    /**
     * Add a media to favorite
     *
     * @param int $media_id
     * @return mixed
     */
    public function addFavorite($media_id) {
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/media/favorites.json',
            array(
                 'media_id' => $media_id,
            ),
            OAUTH_HTTP_METHOD_POST
        );
        return json_decode($this->getLastResponse())->media;

    }

    /**
     * Remove a media from favorites
     *
     * @param int $media_id
     * @return mixed
     */
    public function removeFavorite($media_id) {
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/media/favorites.json',
            array(
                 'media_id' => $media_id,
            ),
            OAUTH_HTTP_METHOD_DELETE
        );
        return json_decode($this->getLastResponse())->media;

    }

    /**
     * Sanitize checkin to transfor id in checkin_id
     *
     * @param $misoCheckin
     * @return mixed
     */
    public function sanitizeCheckins($misoCheckins) {
        if (is_array($misoCheckins)) {
            foreach($misoCheckins as $offest => $misoCheckin) {
                $misoCheckins[$offest] = $this->sanitizeCheckins($misoCheckin);
            }

        } else {
            if (isset($misoCheckins->checkin)) {
                $misoCheckins->checkin->checkin_id = $misoCheckins->checkin->id;
                unset($misoCheckins->checkin->id);
            } else {
                $misoCheckins->checkin_id = $misoCheckins->id;
                unset($misoCheckins->id);

            }
        }

        return $misoCheckins;
    }
}
