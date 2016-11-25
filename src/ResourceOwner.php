<?php
namespace Ely\OAuth2\Client;

use DateTime;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class ResourceOwner implements ResourceOwnerInterface
{
    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array $response
     */
    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    /**
     * Get resource owner id
     *
     * @return string
     */
    public function getId()
    {
        return $this->response['id'];
    }

    /**
     * Get resource owner UUID
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->response['uuid'];
    }

    /**
     * Get resource owner current username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->response['username'];
    }

    /**
     * Get resource owner confirmed E-mail. If you do not have permission 'account_email',
     * then you will get null
     *
     * @return string|null
     */
    public function getEmail()
    {
        return isset($this->response['email']) ? $this->response['email'] : null;
    }

    /**
     * Get resource owner registration date.
     *
     * @return DateTime
     */
    public function getRegisteredAt()
    {
        return new DateTime('@' . $this->response['registeredAt']);
    }

    /**
     * Link to resource owner Ely.by profile
     *
     * @return string
     */
    public function getProfileLink()
    {
        return $this->response['profileLink'];
    }

    /**
     * Get resource owner preferred language, that he used on Ely.by
     * Language codes correspond to ISO 639-1 standard
     *
     * @return string
     */
    public function getPreferredLanguage()
    {
        return $this->response['preferredLanguage'];
    }

    /**
     * Get resource owner current skin url.
     * Remember that this is not a direct link to skin file.
     *
     * @return string
     */
    public function getSkinUrl()
    {
        return "http://skinsystem.ely.by/skins/{$this->getUsername()}.png";
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->response, [
            'skinUrl'   => $this->getSkinUrl(),
        ]);
    }
}
