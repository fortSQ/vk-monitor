<?php

class VK {
    private $clientId;
    private $accessToken;

    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getLinkForAccessToken(array $scope = [])
    {
        $getParams = http_build_query([
            'client_id'     => $this->clientId,
            'redirect_uri'  => 'https://oauth.vk.com/blank.html',
            'response_type' => 'token',
            'scope'         => implode(',', $scope),
        ]);
        $url = "https://oauth.vk.com/authorize?{$getParams}";

        return $url;
    }

    public function methodUsersGet(array $userIdList = [], array $fields = ['online'])
    {
        $getParams = http_build_query([
            'user_ids'      => implode(',', $userIdList),
            'fields'        => implode(',', $fields),
            'access_token'  => $this->accessToken,
        ]);
        $url = "https://api.vk.com/method/users.get?{$getParams}";
        $response = json_decode(file_get_contents($url), true)['response'];
        
        $result = [];
        foreach ($response as $user) {
            $result[$user['uid']] = [
                'is_online' => (bool) $user['online'],
                'is_mobile' => isset($user['online_mobile']),
            ];
        }

        return $result;
    }
}
