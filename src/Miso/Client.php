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
     * Get user from Api
     * @throws \Exception
     * @return stdClass
     */
    public function getUser() {
        $this->fetch('http://gomiso.com/api/oauth/v1/users/show.json');
        $user = json_decode($this->getLastResponse())->user;

        if(!$user) {
            throw new \Exception('User not found');
        }

        return $user;
    }

    /**
     * Search for series
     *
     * @param string $query
     * @return array
     */
    public function searchSeries($query) {
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
     * Get serie details
     *
     * @param int $serie_id
     * @return array
     */
    public function getSerie($serie_id) {
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/media/show.json',
            array(
                 'media_id' => $serie_id,
            )
        );
       return json_decode($this->getLastResponse())->media;
    }

    /**
     * Get episodes for a serie
     *
     * @param int $serie_id
     * @param int $count
     * @return array
     */
    public function getEpisodes($serie_id, $count = 0) {
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/episodes.json',
            array(
                 'media_id' => $serie_id,
                 'count' => $count,
            )
        );
       return json_decode($this->getLastResponse());
    }


    /**
     * Get episodes for a specific season
     *
     * @param int $serie_id
     * @param int $season
     * @return array
     */
    public function getEpisodesBySeason($serie_id, $season) {
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/episodes.json',
            array(
                 'media_id' => $serie_id,
                 'season_num' => $season,
            )
        );
        return json_decode($this->getLastResponse())->episodes;
    }

    /**
     * Get an episode for a serie by season and number
     * @param $serie_id
     * @param $season
     * @param $number
     * @return stdClass
     */
    public function getEpisode($serie_id, $season, $number) {
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/episodes/show.json',
            array(
                 'media_id' => $serie_id,
                 'season_num' => $season,
                 'episode_num' => $number,
            )
        );
        return json_decode($this->getLastResponse())->episode;

    }

    /**
     * Get favorites from Api
     *
     * @return array
     */
    public function getFavorites() {
        $this->fetch('http://gomiso.com/api/oauth/v1/media/favorites.json');
        $favorites = json_decode($this->getLastResponse());

        if(!$favorites) {
            throw new \Exception('No favorites found');
        }

        return $favorites;
    }

    /**
     * Add a serie to favorite
     *
     * @param int $serie_id
     * @return mixed
     */
    public function addFavorite($serie_id) {
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/media/favorites.json',
            array(
                 'media_id' => $serie_id,
            ),
            OAUTH_HTTP_METHOD_POST
        );
        return json_decode($this->getLastResponse())->media;

    }

    /**
     * Remove a serie from favorites
     *
     * @param int $serie_id
     * @return mixed
     */
    public function removeFavorite($serie_id) {
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/media/favorites.json',
            array(
                 'media_id' => $serie_id,
            ),
            OAUTH_HTTP_METHOD_DELETE
        );
        return json_decode($this->getLastResponse())->media;

    }

    /**
     * Get checkins for a serie
     *
     * @param int $serie_id
     * @param int $user_id
     * @param int $last_checkin
     * @return array
     */
    public function getCheckins($serie_id, $user_id, $last_checkin = null) {
        $checkinsParams = array();
        $checkinsParams['media_id'] = $serie_id;
        $checkinsParams['user_id'] = $user_id;
        $checkinsParams['count'] = 50;
        if ($last_checkin != null) {
            $checkinsParams['since_id'] = $last_checkin;
        }
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/checkins.json',
            $checkinsParams
        );
        return $this->sanitizeCheckins(json_decode($this->getLastResponse()));
    }


    /**
     * Post a checking
     * @param int $serie_id
     * @param int $season
     * @param int $number
     * @return stdClass
     */
    public function addCheckin($serie_id, $season, $number)
    {
        $this->fetch(
            'http://gomiso.com/api/oauth/v1/checkins.json',
            array(
                 'media_id' => $serie_id,
                 'season_num' => $season,
                 'episode_num' => $number,
            ),
            OAUTH_HTTP_METHOD_POST
        );
        return $this->sanitizeCheckins(json_decode($this->getLastResponse())->checkin);

    }
}
