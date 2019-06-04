<?php
namespace Allegro\REST;

class Resource
{

    /**
     * Resource constructor.
     * @param string $id
     * @param Resource $parent
     */
    public function __construct($id, Resource $parent)
    {
        $this->id = $id;
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->parent->getAccessToken();
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->parent->getUri() . '/' . $this->id;
    }

    /**
     * @return string
     */
    public function getUploadUri()
    {
        return $this->parent->getUploadUri() . '/' . $this->id;
    }

    /**
     * @return Commands
     */
    public function commands()
    {
        return new Commands($this);
    }

    /**
     * @param null|array $data
     * @param int $version
     * @param bool $beta
     * @return bool|string
     */
    public function get($data = null, $version = 1, $beta = false)
    {
        $uri = $this->getUri();

        if ($data !== null) {
            $uri .= '?';
            $uri .= http_build_query($data);
        }

        return $this->sendApiRequest($uri, 'GET', array(), $version, $beta);
    }

    /**
     * @param array $data
     * @param int $version
     * @param bool $beta
     * @return bool|string
     */
    public function put($data, $version = 1, $beta = false)
    {
        return $this->sendApiRequest($this->getUri(), 'PUT', $data, $version, $beta);
    }

    /**
     * @param array $data
     * @param int $version
     * @param bool $beta
     * @return bool|string
     */
    public function post($data, $version = 1, $beta = false)
    {
        return $this->sendApiRequest($this->getUri(), 'POST', $data, $version, $beta);
    }

    /**
     * @param array $data
     * @param int $version
     * @param bool $beta
     * @return bool|string
     */
    public function upload($data, $version = 1, $beta = false)
    {
        return $this->sendApiRequest($this->getUploadUri(), 'POST', $data, $version, $beta);
    }

    /**
     * @param null|array $data
     * @param int $version
     * @param bool $beta
     * @return bool|string
     */
    public function delete($data = null, $version = 1, $beta = false)
    {
        $uri = $this->getUri();

        if ($data !== null) {
            $uri .= '?';
            $uri .= http_build_query($data);
        }

        return $this->sendApiRequest($uri, 'DELETE', array(), $version, $beta);
    }

    public function __get($name)
    {
        return new Resource($name, $this);
    }

    public function __call($name, $args)
    {
        $id = array_shift($args);
        $collection = new Resource($name, $this);
        return new Resource($id, $collection);
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $data
     * @param int $version
     * @param bool $beta
     * @return bool|string
     */
    protected function sendApiRequest($url, $method, $data = array(), $version = 1, $beta = false)
    {
        $token = $this->getAccessToken();

        $headers = array(
            "Authorization: Bearer $token",
            "Content-Type: application/vnd.allegro.".($beta?'beta':'public').".v".$version."+json",
            "Accept: application/vnd.allegro.".($beta?'beta':'public').".v".$version."+json"
        );

        $data = json_encode($data);

        return $this->sendHttpRequest($url, $method, $headers, $data);
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $headers
     * @param string $data
     * @return bool|string
     */
    protected function sendHttpRequest($url, $method, $headers = array(), $data = '')
    {
        $options = array(
            'http' => array(
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $data,
                'ignore_errors' => true
            )
        );

        $context = stream_context_create($options);

        return file_get_contents($url, false, $context);
    }

    /**
     * @var string
     */
    private $id;

    /**
     * @var Resource
     */
    private $parent;
}
