<?php

namespace App\Classes;

use Carbon\Carbon;
use DB;

class Utils
{

    public function __construct(\GuzzleHttp\Client $client)
    {
        $this->client = $client;
    }

    public function handle()
    {

        $posts = $this->getPostsFb();

        $this->savePosts($posts);
    }

    public function savePosts($posts)
    {

        foreach ($posts as $data) {
            try {
                $data['created_time'] = Carbon::parse($data['created_time']);
                DB::table('posts')->insert($data);
            } catch (\Exception $e) {
                dump($e->getMessage());
            }
        }
    }

    public function getPostLikes($post_id)
    {

        $response = $this->client->get(
            'https://graph.facebook.com/v2.9/' . $post_id . '/reactions',
            [
                'query' => [
                    'access_token' => env('FB_TOKEN'),
                    'summary' => 'true',
                    'limit' => 0,
                ],
            ]
        );
        $response = json_decode($response->getBody(), true);
        if (!isset($response['summary'])) {
            return 0;
        }

        return $response['summary']['total_count'];
    }

    public function mapPosts($posts)
    {
        foreach ($posts as $key => $post) {
            $posts[$key]['likes'] = $this->getPostLikes($post['id']);
        }
        return $posts;
    }

    public function getPostsFb()
    {
        $url = null;

        while (true) {
            if (is_null($url)) {
                $response = $this->client->get(
                    'https://graph.facebook.com/v2.9/ooredootn/posts',
                    [
                        'query' => [

                            'access_token' => env('FB_TOKEN'),
                            'fields' => 'created_time,id,message,full_picture',
                            'since' => Carbon::now()->subYear()->timestamp,
                            'limit' => 100,
                        ],
                    ]
                );

                $response = json_decode($response->getBody(), true);
                $data = $this->mapPosts($response['data']);
                $results = collect($data);
            } else {
                $response = $this->client->get($url);
                $response = json_decode($response->getBody(), true);
                $data = $this->mapPosts($response['data']);
                $results = $results->merge($data);
            }

            unset($data);

            if (isset($response['paging']) && isset($response['paging']['next'])) {
                $url = $response['paging']['next'];
            } else {
                return $results;
            }
        }
    }
}
