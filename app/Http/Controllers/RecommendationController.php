<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RecommendationController extends Controller
{

    public function recommended(Request $request)
    {
        try {
            if (!$request->has('city') && !($request->has('lat') && $request->has('long'))) throw new \Exception('Please provide a valid city or lat/long', 400);

            if ($request->has('city')) {
                $city = $request->input('city');
                $conditions = "q=$city";
            }

            if ($request->has('lat') && $request->has('long')) {
                $lat  = $request->input('lat');
                $long = $request->input('long');
                $conditions = "lat=$lat&lon=$long";
            }

            // temperatura do local recebido
            $temperatura = json_decode($this->getClimate($conditions))->main->temp;
            $token = $this->getSpotifyToken(); // Token spotify

            // array de musicas
            $tracks = $this->getTracks($this->getPlaylists($this->tema($temperatura), $token), $token)->items;

            $songs = function ($musica) {
                $song = new \StdClass();
                $song->name = $musica->track->name . ' - ' . $musica->track->artists[0]->name;
                $song->href = $musica->track->external_urls->spotify;
                return $song;
            };

            $songList = array_map($songs, $tracks);

            return response()->json(['recomended' => $songList], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Seta a querystring para busca dos dados de temperatura na api OPENWEATHERMAP_APPID
     * @param string $conditions
     * 
     * 
     */
    private function getClimate($conditions)
    {
        $apiKey = env('OPENWEATHERMAP_APPID');
        $url = "api.openweathermap.org/data/2.5/weather?$conditions&units=metric&appid=$apiKey";

        return $this->apiGetRequest($url, false);
    }

    /**
     * Retorna genero musical com base na temperatura
     * 
     * @param string $temp
     * 
     * @return string genero musical
     */
    private function tema($temp)
    {
        if ($temp > 30) return 'Festa';
        if ($temp >= 15 && $temp <= 30) return 'Pop';
        if ($temp >= 10 && $temp <= 14) return 'Rock';
        if ($temp < 10) return 'Classicas';
    }

    /**
     * Busca playlist com o genero musical e retorna seu id
     * 
     * @param string $type
     * @param string $token
     * @return string  id de uma playlist aletoria do tema
     */
    private function getPlaylists($type, $token)
    {
        $limite = 10;
        $url = "https://api.spotify.com/v1/search?q=$type&type=playlist&limit=$limite";
        $header = ["Authorization: Bearer $token"];

        return (json_decode($this->apiGetRequest($url, $header))->playlists->items[rand(0, $limite - 1)]->id);
    }

    /**
     * Busca as musicas pertencentes a playlist ${id}
     * 
     * @param string $id
     * @param string $token
     * @return object musicas da playlist
     */
    private function getTracks($id, $token)
    {
        // Pega 10 musicas da playlist
        $limite = 10;
        $url = "https://api.spotify.com/v1/playlists/$id/tracks?limit=$limite";
        $header = ["Authorization: Bearer $token"];
        return json_decode($this->apiGetRequest($url, $header));
    }

    /**
     * Requisição de token Bearer a api do spotify para poder utilizar as demais rotas
     * 
     * @return string token de autenticação
     */
    private function getSpotifyToken()
    {
        $client_id = env('SPOTIFY_CLIENT_ID');
        $client_secret = env('SPOTIFY_CLIENT_SECRET');
        $base64 = base64_encode("$client_id:$client_secret");

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://accounts.spotify.com/api/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Basic $base64",
                'Content-Type: application/x-www-form-urlencoded',
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $token = json_decode($response)->access_token;
        return $token;
    }

    /**
     * Faz requisições do tipo GET para a api OPENWEATHERMAP e SPOTIFY
     * 
     * @return string json response
     */
    private function apiGetRequest($url, $header)
    {
        $curl = curl_init();

        $curlOptionsHeader = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => $header
        ];

        $curlOptions = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ];

        $header ? curl_setopt_array($curl, $curlOptionsHeader) : curl_setopt_array($curl, $curlOptions);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
