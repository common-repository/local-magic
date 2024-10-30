<?php

//namespace App;
//use \DateTime;
//use \LogicException;

/**
 * Universal Feed Writer
 *
 * Item class - Used as feed element in Feed class
 *
 * @package         UniversalFeedWriter
 * @author          Anis uddin Ahmad <anisniit@gmail.com>
 * @link            http://www.ajaxray.com/projects/rss
 */
class Item
{
    /**
    * Collection of feed item elements
    */
    private $elements = array();

    /**
    * Contains the format of this feed.
    */
    private $version;

    /**
    * Is used as a suffix when multiple elements have the same name.
    **/
    private $_cpt = 0;

    /**
    * Constructor
    *
    * @param string $version constant (RSS1/RSS2/ATOM) RSS2 is default.
    */
    public function __construct($version = Feed::RSS2)
    {
        $this->version = $version;
    }

    /**
    * Return an unique number
    *
    * @access   private
    * @return   int
    **/
    private function cpt()
    {
        return $this->_cpt++;
    }

    /**
    * Add an element to elements array
    *
    * @access   public
    * @param    string $elementName    The tag name of an element
    * @param    string $content        The content of tag
    * @param    array  $attributes     Attributes (if any) in 'attrName' => 'attrValue' format
    * @param    boolean $overwrite     Specifies if an already existing element is overwritten.
    * @param    boolean $allowMultiple Specifies if multiple elements of the same name are allowed.
    * @return   self
    * @throws   \InvalidArgumentException if the element name is not a string, empty or NULL.
    */
    public function addElement($elementName, $content, array $attributes = null, $overwrite = FALSE, $allowMultiple = FALSE)
    {
        if (empty($elementName))
            throw new \InvalidArgumentException('The element name may not be empty or NULL.');
        if (!is_string($elementName))
            throw new \InvalidArgumentException('The element name must be a string.');

        $key = $elementName;

        // return if element already exists & if overwriting is disabled
        // & if multiple elements are not allowed.
        if (isset($this->elements[$elementName]) && !$overwrite) {
            if (!$allowMultiple)
                return $this;

            $key .= '-' . $this->cpt();
        }

        $this->elements[$key]['name']       = $elementName;
        $this->elements[$key]['content']    = $content;
        $this->elements[$key]['attributes'] = $attributes;

        return $this;
    }

    /**
    * Set multiple feed elements from an array.
    * Elements which have attributes cannot be added by this method
    *
    * @access   public
    * @param    array   array of elements in 'tagName' => 'tagContent' format.
    * @return   self
    */
    public function addElementArray(array $elementArray)
    {
        foreach ($elementArray as $elementName => $content) {
            $this->addElement($elementName, $content);
        }

        return $this;
    }

    /**
    * Return the collection of elements in this feed item
    *
    * @access   public
    * @return   array   All elements of this item.
    * @throws   InvalidOperationException on ATOM feeds if either a content or link element is missing.
    * @throws   InvalidOperationException on RSS1 feeds if a title or link element is missing.
    */
    public function getElements()
    {
        // ATOM feeds have some specific requirements...
        if ($this->version == Feed::ATOM)
        {
            // Add an 'id' element, if it was not added by calling the setLink method.
            // Use the value of the title element as key, since no link element was specified.
            if (!array_key_exists('id', $this->elements))
                $this->setId(Feed::uuid($this->elements['title']['content'], 'urn:uuid:'));

            // Either a 'link' or 'content' element is needed.
            if (!array_key_exists('content', $this->elements) && !array_key_exists('link', $this->elements))
                throw new InvalidOperationException('ATOM feed entries need a link or a content element. Call the setLink or setContent method.');
        }
        // ...same with RSS1 feeds.
        else if ($this->version == Feed::RSS1)
        {
            if (!array_key_exists('title', $this->elements))
                throw new InvalidOperationException('RSS1 feed entries need a title element. Call the setTitle method.');
            if (!array_key_exists('link', $this->elements))
                throw new InvalidOperationException('RSS1 feed entries need a link element. Call the setLink method.');
        }

        return $this->elements;
    }

    /**
    * Return the type of this feed item
    *
    * @access   public
    * @return   string  The feed type, as defined in Feed.php
    */
    public function getVersion()
    {
        return $this->version;
    }

    // Wrapper functions ------------------------------------------------------

    /**
    * Set the 'description' element of feed item
    *
    * @access   public
    * @param    string $description The content of the 'description' or 'summary' element
    * @return   self
    */
    public function setDescription($description)
    {
        $tag = ($this->version == Feed::ATOM) ? 'summary' : 'description';

        return $this->addElement($tag, $description);
    }

    /**
    * Set the 'content' element of the feed item
    * For ATOM feeds only
    *
    * @access   public
    * @param    string $content Content for the item (i.e., the body of a blog post).
    * @return   self
    * @throws   InvalidOperationException if this method is called on non-ATOM feeds.
    */
    public function setContent($content)
    {
        if ($this->version != Feed::ATOM)
            throw new InvalidOperationException('The content element is supported in ATOM feeds only.');

        return $this->addElement('content', $content, array('type' => 'html'));
    }

    /**
    * Set the 'title' element of feed item
    *
    * @access   public
    * @param    string $title The content of 'title' element
    * @return   self
    */
    public function setTitle($title)
    {
        return $this->addElement('title', $title);
    }

    /**
    * Set the 'date' element of the feed item.
    *
    * The value of the date parameter can be either an instance of the
    * DateTime class, an integer containing a UNIX timestamp or a string
    * which is parseable by PHP's 'strtotime' function.
    *
    * @access   public
    * @param    DateTime|int|string $date Date which should be used.
    * @return   self
    * @throws   \InvalidArgumentException if the given date was not parseable.
    */
    public function setDate($date)
    {
        if (!is_numeric($date)) {
            if ($date instanceof DateTime)
                $date = $date->getTimestamp();
            else {
                $date = strtotime($date);

                if ($date === FALSE)
                    throw new \InvalidArgumentException('The given date string was not parseable.');
            }
        } elseif ($date < 0)
            throw new \InvalidArgumentException('The given date is not an UNIX timestamp.');

        if ($this->version == Feed::ATOM) {
            $tag    = 'updated';
            $value  = date(\DATE_ATOM, $date);
        } elseif ($this->version == Feed::RSS2) {
            $tag    = 'pubDate';
            $value  = date(\DATE_RSS, $date);
        } else {
            $tag    = 'dc:date';
            $value  = date("Y-m-d", $date);
        }

        return $this->addElement($tag, $value);
    }

    /**
    * Set the 'link' element of feed item
    *
    * @access   public
    * @param    string $link The content of 'link' element
    * @return   self
    */
    public function setLink($link)
    {
        if ($this->version == Feed::RSS2 || $this->version == Feed::RSS1) {
            $this->addElement('link', $link);
        } else {
            $this->addElement('link','',array('href'=>$link));
            $this->setId(Feed::uuid($link,'urn:uuid:'));
        }

        return $this;
    }

    /**
    * Attach a external media to the feed item.
    * Not supported in RSS 1.0 feeds.
    *
    * See RFC 4288 for syntactical correct MIME types.
    *
    * Note that you should avoid the use of more than one enclosure in one item,
    * since some RSS aggregators don't support it.
    *
    * @access   public
    * @param    string $url       The URL of the media.
    * @param    integer $length   The length of the media.
    * @param    string  $type     The MIME type attribute of the media.
    * @param    boolean $multiple Specifies if multiple enclosures are allowed
    * @return   self
    * @link     https://tools.ietf.org/html/rfc4288
    * @throws   \InvalidArgumentException if the length or type parameter is invalid.
    * @throws   InvalidOperationException if this method is called on RSS1 feeds.
    */
    public function addEnclosure($url, $length, $type, $multiple = TRUE)
    {
        if ($this->version == Feed::RSS1)
            throw new InvalidOperationException('Media attachment is not supported in RSS1 feeds.');

        // the length parameter should be set to 0 if it can't be determined
        // see http://www.rssboard.org/rss-profile#element-channel-item-enclosure
        if (!is_numeric($length) || $length < 0)
            throw new \InvalidArgumentException('The length parameter must be an integer and greater or equals to zero.');

        // Regex used from RFC 4287, page 41
        if (!is_string($type) || preg_match('/.+\/.+/', $type) != 1)
            throw new \InvalidArgumentException('type parameter must be a string and a MIME type.');

        $attributes = array('length' => $length, 'type' => $type);

        if ($this->version == Feed::RSS2) {
            $attributes['url'] = $url;
            $this->addElement('enclosure', '', $attributes, FALSE, $multiple);
        } else {
            $attributes['href'] = $url;
            $attributes['rel'] = 'enclosure';
            $this->addElement('atom:link', '', $attributes, FALSE, $multiple);
        }

        return $this;
    }

    /**
    * Set the 'author' element of feed item.
    * Not supported in RSS 1.0 feeds.
    *
    * @access   public
    * @param    string $author The author of this item
    * @param    string|null $email Optional email address of the author
    * @param    string|null $uri Optional URI related to the author
    * @return   self
    * @throws   \InvalidArgumentException if the provided email address is syntactically incorrect.
    * @throws   InvalidOperationException if this method is called on RSS1 feeds.
    */
    public function setAuthor($author, $email = null, $uri = null)
    {
        if ($this->version == Feed::RSS1)
            throw new InvalidOperationException('The author element is not supported in RSS1 feeds.');

        // Regex from RFC 4287 page 41
        if ($email != null && preg_match('/.+@.+/', $email) != 1)
            throw new \InvalidArgumentException('The email address is syntactically incorrect.');

        if ($this->version == Feed::RSS2)
        {
            if ($email != null)
                $author = $email . ' (' . $author . ')';

            $this->addElement('author', $author);
        }
        else
        {
            $elements = array('name' => $author);

            if ($email != null)
                $elements['email'] = $email;

            if ($uri != null)
                $elements['uri'] = $uri;

            $this->addElement('author', $elements);
        }

        return $this;
    }

    /**
    * Set the unique identifier of the feed item
    *
    * On ATOM feeds, the identifier must begin with an valid URI scheme.
    *
    * @access   public
    * @param    string $id         The unique identifier of this item
    * @param    boolean $permaLink The value of the 'isPermaLink' attribute in RSS 2 feeds.
    * @return   self
    * @throws   \InvalidArgumentException if the permaLink parameter is not boolean.
    * @throws   InvalidOperationException if this method is called on RSS1 feeds.
    */
    public function setId($id, $permaLink = false)
    {
        if ($this->version == Feed::RSS2) {
            if (!is_bool($permaLink))
                throw new \InvalidArgumentException('The permaLink parameter must be boolean.');

            $permaLink = $permaLink ? 'true' : 'false';

            $this->addElement('guid', $id, array('isPermaLink' => $permaLink));
        } elseif ($this->version == Feed::ATOM) {
            // Check if the given ID is an valid URI scheme (see RFC 4287 4.2.6)
            // The list of valid schemes was generated from http://www.iana.org/assignments/uri-schemes
            // by using only permanent or historical schemes.
            $validSchemes = array('aaa', 'aaas', 'about', 'acap', 'acct', 'cap', 'cid', 'coap', 'coaps', 'crid', 'data', 'dav', 'dict', 'dns', 'example', 'fax', 'file', 'filesystem', 'ftp', 'geo', 'go', 'gopher', 'h323', 'http', 'https', 'iax', 'icap', 'im', 'imap', 'info', 'ipp', 'ipps', 'iris', 'iris.beep', 'iris.lwz', 'iris.xpc', 'iris.xpcs', 'jabber', 'ldap', 'mailserver', 'mailto', 'mid', 'modem', 'msrp', 'msrps', 'mtqp', 'mupdate', 'news', 'nfs', 'ni', 'nih', 'nntp', 'opaquelocktoken', 'pack', 'pkcs11', 'pop', 'pres', 'prospero', 'reload', 'rtsp', 'rtsps', 'rtspu', 'service', 'session', 'shttp', 'sieve', 'sip', 'sips', 'sms', 'snews', 'snmp', 'soap.beep', 'soap.beeps', 'stun', 'stuns', 'tag', 'tel', 'telnet', 'tftp', 'thismessage', 'tip', 'tn3270', 'turn', 'turns', 'tv', 'urn', 'vemmi', 'videotex', 'vnc', 'wais', 'ws', 'wss', 'xcon', 'xcon-userid', 'xmlrpc.beep', 'xmlrpc.beeps', 'xmpp', 'z39.50', 'z39.50r', 'z39.50s');
            $found = FALSE;
            $checkId = strtolower($id);

            foreach($validSchemes as $scheme)
                if (strrpos($checkId, $scheme . ':', -strlen($checkId)) !== FALSE)
                {
                    $found = TRUE;
                    break;
                }

            if (!$found)
                throw new \InvalidArgumentException("The ID must begin with an IANA-registered URI scheme.");

            $this->addElement('id', $id, NULL, TRUE);
        } else
            throw new InvalidOperationException('A unique ID is not supported in RSS1 feeds.');

        return $this;
    }

 } // end of class Item
 
 
 
/**
 * Universal Feed Writer class
 *
 * Generate RSS 1.0, RSS2.0 and ATOM Feeds
 *
 * @package     UniversalFeedWriter
 * @author      Anis uddin Ahmad <anisniit@gmail.com>
 * @link        http://www.ajaxray.com/projects/rss
 */ 
abstract class Feed
{
    // RSS 0.90  Officially obsoleted by 1.0
    // RSS 0.91, 0.92, 0.93 and 0.94  Officially obsoleted by 2.0
    // So, define constants for RSS 1.0, RSS 2.0 and ATOM

    const RSS1 = 'RSS 1.0';
    const RSS2 = 'RSS 2.0';
    const ATOM = 'ATOM';

    const VERSION = '1.1.0';

    /**
    * Collection of all channel elements
    */
    private $channels      = array();

    /**
    * Collection of items as object of \FeedWriter\Item class.
    */
    private $items         = array();

    /**
    * Collection of other version wise data.
    *
    * Currently used to store the 'rdf:about' attribute and image element of the channel (both RSS1 only).
    */
    private $data          = array();

    /**
    * The tag names which have to encoded as CDATA
    */
    private $CDATAEncoding = array();

    /**
    * Collection of XML namespaces
    */
    private $namespaces    = array();

    /**
    * Contains the format of this feed.
    */
    private $version       = null;

    /**
     * Constructor
     *
     * If no version is given, a feed in RSS 2.0 format will be generated.
     *
     * @param string $version the version constant (RSS1/RSS2/ATOM).
     */
    protected function __construct($version = Feed::RSS2)
    {
        $this->version = $version;

        // Setting default encoding
        $this->encoding = 'utf-8';

        // Setting default value for essential channel element
        $this->setTitle($version . ' Feed');

        // Add some default XML namespaces
        $this->namespaces['content'] = 'http://purl.org/rss/1.0/modules/content/';
        $this->namespaces['wfw'] = 'http://wellformedweb.org/CommentAPI/';
        $this->namespaces['atom'] = 'http://www.w3.org/2005/Atom';
        $this->namespaces['rdf'] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
        $this->namespaces['rss1'] = 'http://purl.org/rss/1.0/';
        $this->namespaces['dc'] = 'http://purl.org/dc/elements/1.1/';
        $this->namespaces['sy'] = 'http://purl.org/rss/1.0/modules/syndication/';

        // Tag names to encode in CDATA
        $this->addCDATAEncoding(array('description', 'content:encoded', 'summary'));
    }

    // Start # public functions ---------------------------------------------

    /**
    * Set the URLs for feed pagination.
    *
    * See RFC 5005, chapter 3. At least one page URL must be specified.
    *
    * @param   string $nextURL The URL to the next page of this feed. Optional.
    * @param   string $previousURL The URL to the previous page of this feed. Optional.
    * @param   string $firstURL The URL to the first page of this feed. Optional.
    * @param   string $lastURL The URL to the last page of this feed. Optional.
    * @link    http://tools.ietf.org/html/rfc5005#section-3
    * @return  self
    * @throws  \LogicException if none of the parameters are set.
     */
    public function setPagination($nextURL = null, $previousURL = null, $firstURL = null, $lastURL = null)
    {
        if (empty($nextURL) && empty($previousURL) && empty($firstURL) && empty($lastURL))
            throw new \LogicException('At least one URL must be specified for pagination to work.');

        if (!empty($nextURL))
            $this->setAtomLink($nextURL, 'next');

        if (!empty($previousURL))
            $this->setAtomLink($previousURL, 'previous');

        if (!empty($firstURL))
            $this->setAtomLink($firstURL, 'first');

        if (!empty($lastURL))
            $this->setAtomLink($lastURL, 'last');

        return $this;
    }

    /**
    * Add a channel element indicating the program used to generate the feed.
    *
    * @return   self
    * @throws   InvalidOperationException if this method is called on an RSS1 feed.
    */
    public function addGenerator()
    {
        if ($this->version == Feed::ATOM)
            $this->setChannelElement('atom:generator', 'FeedWriter', array('uri' => 'https://github.com/mibe/FeedWriter'));
        else if ($this->version == Feed::RSS2)
            $this->setChannelElement('generator', 'FeedWriter');
        else
            throw new InvalidOperationException('The generator element is not supported in RSS1 feeds.');

        return $this;
    }

    /**
    * Add a XML namespace to the internal list of namespaces. After that,
    * custom channel elements can be used properly to generate a valid feed.
    *
    * @access   public
    * @param    string $prefix namespace prefix
    * @param    string $uri namespace name (URI)
    * @return   self
    * @link     http://www.w3.org/TR/REC-xml-names/
    * @throws   \InvalidArgumentException if the prefix or uri is empty or NULL.
    */
    public function addNamespace($prefix, $uri)
    {
        if (empty($prefix))
            throw new \InvalidArgumentException('The prefix may not be emtpy or NULL.');
        if (empty($uri))
            throw new \InvalidArgumentException('The uri may not be empty or NULL.');

        $this->namespaces[$prefix] = $uri;

        return $this;
    }

    /**
    * Add a channel element to the feed.
    *
    * @access   public
    * @param    string $elementName name of the channel tag
    * @param    string $content content of the channel tag
    * @param    array   array of element attributes with attribute name as array key
    * @param    bool    TRUE if this element can appear multiple times
    * @return   self
    * @throws   \InvalidArgumentException if the element name is not a string, empty or NULL.
    */
    public function setChannelElement($elementName, $content, array $attributes = null, $multiple = false)
    {
        if (empty($elementName))
            throw new \InvalidArgumentException('The element name may not be empty or NULL.');
        if (!is_string($elementName))
            throw new \InvalidArgumentException('The element name must be a string.');

        $entity['content'] = $content;
        $entity['attributes'] = $attributes;

        if ($multiple === TRUE)
            $this->channels[$elementName][] = $entity;
        else
            $this->channels[$elementName] = $entity;

        return $this;
    }

    /**
    * Set multiple channel elements from an array. Array elements
    * should be 'channelName' => 'channelContent' format.
    *
    * @access   public
    * @param    array   array of channels
    * @return   self
    */
    public function setChannelElementsFromArray(array $elementArray)
    {
        foreach ($elementArray as $elementName => $content) {
            $this->setChannelElement($elementName, $content);
        }

        return $this;
    }

    /**
    * Get the appropriate MIME type string for the current feed.
    *
    * @access   public
    * @return   string  The MIME type string.
    */
    public function getMIMEType()
    {
        switch ($this->version) {
            case Feed::RSS2 : $mimeType = "application/rss+xml";
                break;
            case Feed::RSS1 : $mimeType = "application/rdf+xml";
                break;
            case Feed::ATOM : $mimeType = "application/atom+xml";
                break;
            default : $mimeType = "text/xml";
        }
        
        return $mimeType;
    }

    /**
    * Print the actual RSS/ATOM file
    *
    * Sets a Content-Type header and echoes the contents of the feed.
    * Should only be used in situations where direct output is desired;
    * if you need to pass a string around, use generateFeed() instead.
    *
    * @access   public
    * @param    bool    FALSE if the specific feed media type should be sent.
    * @return   void
    * @throws   \InvalidArgumentException if the useGenericContentType parameter is not boolean.
    */
    public function printFeed($useGenericContentType = false)
    {
        if (!is_bool($useGenericContentType))
            throw new \InvalidArgumentException('The useGenericContentType parameter must be boolean.');

        $contentType = "text/xml";

        if (!$useGenericContentType) {
            $contentType = $this->getMIMEType();
        }

        // Generate the feed before setting the header, so Exceptions will be nicely visible.
        
        $feed = $this->generateFeed();
        //header("Content-Type: " . $contentType . "; charset=" . $this->encoding);
        header('Content-Type: '.feed_content_type('rss-http').'; charset='.get_option('blog_charset'), true);
        echo $feed;
    }

    /**
    * Generate the feed.
    *
    * @access   public
    * @return   string  The complete feed XML.
    * @throws   InvalidOperationException if the link element of the feed is not set.
    */
    public function generateFeed()
    {
        if ($this->version != Feed::ATOM && !array_key_exists('link', $this->channels))
            throw new InvalidOperationException('RSS1 & RSS2 feeds need a link element. Call the setLink method before this method.');

        return $this->makeHeader()
            . $this->makeChannels()
            . $this->makeItems()
            . $this->makeFooter();
    }

    /**
    * Create a new Item.
    *
    * @access   public
    * @return   Item  instance of Item class
    */
    public function createNewItem()
    {
        $Item = new Item($this->version);

        return $Item;
    }

    /**
     * Add one or more tags to the list of CDATA encoded tags
     *
     * @access  public
     * @param   array  $tags An array of tag names that are merged into the list of tags which should be encoded as CDATA
     * @return  self
     */
    public function addCDATAEncoding(array $tags)
    {
        $this->CDATAEncoding = array_merge($this->CDATAEncoding, $tags);

        return $this;
    }

    /**
     * Get list of CDATA encoded properties
     *
     * @access  public
     * @return  array   Return an array of CDATA properties that are to be encoded as CDATA
     */
    public function getCDATAEncoding()
    {
        return $this->CDATAEncoding;
    }

    /**
     * Remove tags from the list of CDATA encoded tags
     *
     * @access  public
     * @param   array  $tags An array of tag names that should be removed.
     * @return  void
     */
    public function removeCDATAEncoding(array $tags)
    {
        // Call array_values to re-index the array.
        $this->CDATAEncoding = array_values(array_diff($this->CDATAEncoding, $tags));
    }

    /**
    * Add a FeedItem to the main class
    *
    * @access   public
    * @param    Item   $feedItem instance of Item class
    * @return   self
    * @throws   \InvalidArgumentException if the given item version mismatches.
    */
    public function addItem(Item $feedItem)
    {
        if ($feedItem->getVersion() != $this->version)
        {
            $msg = sprintf('Feed type mismatch: This instance can handle %s feeds only, but item for %s feeds given.', $this->version, $feedItem->getVersion());
            throw new \InvalidArgumentException($msg);
        }

        $this->items[] = $feedItem;

        return $this;
    }

    // Wrapper functions -------------------------------------------------------------------

    /**
    * Set the 'encoding' attribute in the XML prolog.
    *
    * @access   public
    * @param    string $encoding value of 'encoding' attribute
    * @return   self
    * @throws   \InvalidArgumentException if the encoding is not a string, empty or NULL.
    */
    public function setEncoding($encoding)
    {
        if (empty($encoding))
            throw new \InvalidArgumentException('The encoding may not be empty or NULL.');
        if (!is_string($encoding))
            throw new \InvalidArgumentException('The encoding must be a string.');

        $this->encoding = $encoding;

        return $this;
    }

    /**
    * Set the 'title' channel element
    *
    * @access   public
    * @param    string $title value of 'title' channel tag
    * @return   self
    */
    public function setTitle($title)
    {
        return $this->setChannelElement('title', $title);
    }

    /**
    * Set the date when the feed was lastly updated.
    *
    * This adds the 'updated' element to the feed. The value of the date parameter
    * can be either an instance of the DateTime class, an integer containing a UNIX
    * timestamp or a string which is parseable by PHP's 'strtotime' function.
    *
    * Not supported in RSS1 feeds.
    *
    * @access   public
    * @param    DateTime|int|string  Date which should be used.
    * @return   self
    * @throws   \InvalidArgumentException if the given date is not an instance of DateTime, a UNIX timestamp or a date string.
    * @throws   InvalidOperationException if this method is called on an RSS1 feed.
    */
    public function setDate($date)
    {
        if ($this->version == Feed::RSS1)
            throw new InvalidOperationException('The publication date is not supported in RSS1 feeds.');

        // The feeds have different date formats.
        $format = $this->version == Feed::ATOM ? \DATE_ATOM : \DATE_RSS;

        if ($date instanceof DateTime)
            $date = $date->format($format);
        else if(is_numeric($date) && $date >= 0)
            $date = date($format, $date);
        else if (is_string($date))
        {
            $timestamp = strtotime($date);
            if ($timestamp === FALSE)
                throw new \InvalidArgumentException('The given date was not parseable.');

            $date = date($format, $timestamp);
        }
        else
            throw new \InvalidArgumentException('The given date is not an instance of DateTime, a UNIX timestamp or a date string.');

        if ($this->version == Feed::ATOM)
            $this->setChannelElement('updated', $date);
        else
            $this->setChannelElement('lastBuildDate', $date);

        return $this;
    }

    /**
    * Set a phrase or sentence describing the feed.
    *
    * @access   public
    * @param    string  $description Description of the feed.
    * @return   self
    */
    public function setDescription($description)
    {
        if ($this->version != Feed::ATOM)
            $this->setChannelElement('description', $description);
        else
            $this->setChannelElement('subtitle', $description);

        return $this;
    }

    /**
    * Set the 'link' channel element
    *
    * @access   public
    * @param    string $link value of 'link' channel tag
    * @return   self
    */
    public function setLink($link)
    {
        if ($this->version == Feed::ATOM)
            $this->setAtomLink($link);
        else
            $this->setChannelElement('link', $link);

        return $this;
    }

    /**
    * Set custom 'link' channel elements.
    *
    * In ATOM feeds, only one link with alternate relation and the same combination of
    * type and hreflang values.
    *
    * @access   public
    * @param    string $href     URI of this link
    * @param    string $rel      relation type of the resource
    * @param    string $type     MIME type of the target resource
    * @param    string $hreflang language of the resource
    * @param    string $title    human-readable information about the resource
    * @param    int    $length   length of the resource in bytes
    * @link     https://www.iana.org/assignments/link-relations/link-relations.xml
    * @link     https://tools.ietf.org/html/rfc4287#section-4.2.7
    * @return   self
    * @throws   \InvalidArgumentException on multiple occasions.
    * @throws   InvalidOperationException if the same link with the same attributes was already added to the feed.
    */
    public function setAtomLink($href, $rel = null, $type = null, $hreflang = null, $title = null, $length = null)
    {
        $data = array('href' => $href);

        if ($rel != null) {
            if (!is_string($rel) || empty($rel))
                throw new \InvalidArgumentException('rel parameter must be a string and a valid relation identifier.');

            $data['rel'] = $rel;
        }
        if ($type != null) {
            // Regex used from RFC 4287, page 41
            if (!is_string($type) || preg_match('/.+\/.+/', $type) != 1)
                throw new \InvalidArgumentException('type parameter must be a string and a MIME type.');

            $data['type'] = $type;
        }
        if ($hreflang != null) {
            // Regex used from RFC 4287, page 41
            if (!is_string($hreflang) || preg_match('/[A-Za-z]{1,8}(-[A-Za-z0-9]{1,8})*/', $hreflang) != 1)
                throw new \InvalidArgumentException('hreflang parameter must be a string and a valid language code.');

            $data['hreflang'] = $hreflang;
        }
        if ($title != null) {
            if (!is_string($title) || empty($title))
                throw new \InvalidArgumentException('title parameter must be a string and not empty.');

            $data['title'] = $title;
        }
        if ($length != null) {
            if (!is_int($length) || $length < 0)
                throw new \InvalidArgumentException('length parameter must be a positive integer.');

            $data['length'] = (string) $length;
        }

        // ATOM spec. has some restrictions on atom:link usage
        // See RFC 4287, page 12 (4.1.1)
        if ($this->version == Feed::ATOM) {
            foreach ($this->channels as $key => $value) {
                if ($key != 'atom:link')
                    continue;

                // $value is an array , so check every element
                foreach ($value as $linkItem) {
                    $attrib = $linkItem['attributes'];
                    // Only one link with relation alternate and same hreflang & type is allowed.
                    if (@$attrib['rel'] == 'alternate' && @$attrib['hreflang'] == $hreflang && @$attrib['type'] == $type)
                        throw new InvalidOperationException('The feed must not contain more than one link element with a'
                        . ' relation of "alternate" that has the same combination of type and hreflang attribute values.');
                }
            }
        }

        return $this->setChannelElement('atom:link', '', $data, true);
    }

    /**
    * Set an 'atom:link' channel element with relation=self attribute.
    * Needs the full URL to this feed.
    *
    * @link     http://www.rssboard.org/rss-profile#namespace-elements-atom-link
    * @access   public
    * @param    string $url URL to this feed
    * @return   self
    */
    public function setSelfLink($url)
    {
        return $this->setAtomLink($url, 'self', $this->getMIMEType());
    }

    /**
    * Set the 'image' channel element
    *
    * @access   public
    * @param    string $url URL of the image
    * @param    string $title Title of the image. RSS only.
    * @param    string $link Link target URL of the image. RSS only.
    * @return   self
    * @throws   \InvalidArgumentException if the url is invalid.
    * @throws   \InvalidArgumentException if the title and link parameter are not a string or empty.
    */
    public function setImage($url, $title = null, $link = null)
    {
        if (!is_string($url) || empty($url))
            throw new \InvalidArgumentException('url parameter must be a string and may not be empty or NULL.');

        // RSS feeds have support for a title & link element.
        if ($this->version != Feed::ATOM)
        {
            if (!is_string($title) || empty($title))
                throw new \InvalidArgumentException('title parameter must be a string and may not be empty or NULL.');
            if (!is_string($link) || empty($link))
                throw new \InvalidArgumentException('link parameter must be a string and may not be empty or NULL.');

            $data = array('title'=>$title, 'link'=>$link, 'url'=>$url);
            $name = 'image';
        }
        else
        {
            $name = 'logo';
            $data = $url;
        }

        // Special handling for RSS1 again (since RSS1 is a bit strange...)
        if ($this->version == Feed::RSS1)
        {
            $this->data['Image'] = $data;
            return $this->setChannelElement($name, '', array('rdf:resource' => $url), false);
        }
        else
            return $this->setChannelElement($name, $data);
    }

    /**
    * Set the channel 'rdf:about' attribute, which is used in RSS1 feeds only.
    *
    * @access   public
    * @param    string $url value of 'rdf:about' attribute of the channel element
    * @return   self
    * @throws   InvalidOperationException if this method is called and the feed is not of type RSS1.
    * @throws   \InvalidArgumentException if the given URL is invalid.
    */
    public function setChannelAbout($url)
    {
       if ($this->version != Feed::RSS1)
            throw new InvalidOperationException("This method is only supported in RSS1 feeds.");
       
        if (empty($url))
            throw new \InvalidArgumentException('The about URL may not be empty or NULL.');
        if (!is_string($url))
            throw new \InvalidArgumentException('The about URL must be a string.');

        $this->data['ChannelAbout'] = $url;

        return $this;
    }

    /**
    * Generate an UUID.
    *
    * The UUID is based on an MD5 hash. If no key is given, a unique ID as the input
    * for the MD5 hash is generated.
    *
    * @author   Anis uddin Ahmad <admin@ajaxray.com>
    * @access   public
    * @param    string $key    optional key on which the UUID is generated
    * @param    string $prefix an optional prefix
    * @return   string         the formatted UUID
    */
    public static function uuid($key = null, $prefix = '')
    {
        $key = ($key == null) ? uniqid(rand()) : $key;
        $chars = md5($key);
        $uuid  = substr($chars,0,8) . '-';
        $uuid .= substr($chars,8,4) . '-';
        $uuid .= substr($chars,12,4) . '-';
        $uuid .= substr($chars,16,4) . '-';
        $uuid .= substr($chars,20,12);

        return $prefix . $uuid;
    }

    /**
    * Replace invalid XML characters.
    *
    * @link http://www.phpwact.org/php/i18n/charsets#xml See utf8_for_xml() function
    * @link http://www.w3.org/TR/REC-xml/#charsets
    * @link https://github.com/mibe/FeedWriter/issues/30
    *
    * @access   public
    * @param    string $string      string which should be filtered
    * @param    string $replacement replace invalid characters with this string
    * @return   string              the filtered string
    */
    public static function filterInvalidXMLChars($string, $replacement = '_') // default to '\x{FFFD}' ???
    {
        $result = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]+/u', $replacement, $string);

        // Did the PCRE replace failed because of bad UTF-8 data?
        // If yes, try a non-multibyte regex and without the UTF-8 mode enabled.
        if ($result == NULL && preg_last_error() == PREG_BAD_UTF8_ERROR)
            $result = preg_replace('/[^\x09\x0a\x0d\x20-\xFF]+/', $replacement, $string);

        // In case the regex replacing failed completely, return the whole unfiltered string.
        if ($result == NULL)
            $result = $string;

        return $result;
    }
    // End # public functions ----------------------------------------------

    // Start # private functions ----------------------------------------------

    /**
    * Returns all used XML namespace prefixes in this instance.
    * This includes all channel elements and feed items.
    * Unfortunately some namespace prefixes are not included,
    * because they are hardcoded, e.g. rdf.
    *
    * @access   private
    * @return   array   Array with namespace prefix as value.
    */
    private function getNamespacePrefixes()
    {
        $prefixes = array();

        // Get all tag names from channel elements...
        $tags = array_keys($this->channels);

        // ... and now all names from feed items
        foreach ($this->items as $item) {
            foreach (array_keys($item->getElements()) as $key) {
                if (!in_array($key, $tags)) {
                    $tags[] = $key;
                }
            }
        }

        // Look for prefixes in those tag names
        foreach ($tags as $tag) {
            $elements = explode(':', $tag);

            if (count($elements) != 2)
                continue;

            $prefixes[] = $elements[0];
        }

        return array_unique($prefixes);
    }

    /**
    * Returns the XML header and root element, depending on the feed type.
    *
    * @access   private
    * @return   string  The XML header of the feed.
    * @throws   InvalidOperationException if an unknown XML namespace prefix is encountered.
    */
    private function makeHeader()
    {
        $out = '<?xml version="1.0" encoding="'.$this->encoding.'" ?>' . PHP_EOL;

        $prefixes = $this->getNamespacePrefixes();
        $attributes = array();
        $tagName = '';
        $defaultNamespace = '';

        if ($this->version == Feed::RSS2) {
            $tagName = 'rss';
            $attributes['version'] = '2.0';
        } elseif ($this->version == Feed::RSS1) {
            $tagName = 'rdf:RDF';
            $prefixes[] = 'rdf';
            $defaultNamespace = $this->namespaces['rss1'];
        } elseif ($this->version == Feed::ATOM) {
            $tagName = 'feed';
            $defaultNamespace = $this->namespaces['atom'];

            // Ugly hack to remove the 'atom' value from the prefixes array.
            $prefixes = array_flip($prefixes);
            unset($prefixes['atom']);
            $prefixes = array_flip($prefixes);
        }

        // Iterate through every namespace prefix and add it to the element attributes.
        foreach ($prefixes as $prefix) {
            if (!isset($this->namespaces[$prefix]))
                throw new InvalidOperationException('Unknown XML namespace prefix: \'' . $prefix . '\'.'
                    . ' Use the addNamespace method to add support for this prefix.');
            else
                $attributes['xmlns:' . $prefix] = $this->namespaces[$prefix];
        }

        // Include default namepsace, if required
        if (!empty($defaultNamespace))
            $attributes['xmlns'] = $defaultNamespace;

        $out .= $this->makeNode($tagName, '', $attributes, true);

        return $out;
    }

    /**
    * Closes the open tags at the end of file
    *
    * @access   private
    * @return   string  The XML footer of the feed.
    */
    private function makeFooter()
    {
        if ($this->version == Feed::RSS2) {
            return '</channel>' . PHP_EOL . '</rss>';
        } elseif ($this->version == Feed::RSS1) {
            return '</rdf:RDF>';
        } elseif ($this->version == Feed::ATOM) {
            return '</feed>';
        }
    }

    /**
    * Creates a single node in XML format
    *
    * @access   private
    * @param    string $tagName    name of the tag
    * @param    mixed  $tagContent tag value as string or array of nested tags in 'tagName' => 'tagValue' format
    * @param    array  $attributes Attributes (if any) in 'attrName' => 'attrValue' format
    * @param    bool $omitEndTag True if the end tag should be omitted. Defaults to false.
    * @return   string  formatted xml tag
    * @throws   \InvalidArgumentException if the tagContent is not an array and not a string.
    */
    private function makeNode($tagName, $tagContent, array $attributes = null, $omitEndTag = false)
    {
        $nodeText = '';
        $attrText = '';

        if ($attributes != null) {
            foreach ($attributes as $key => $value) {
                $value = self::filterInvalidXMLChars($value);
                $value = htmlspecialchars($value);
                $attrText .= " $key=\"$value\"";
            }
        }

        $attrText .= (in_array($tagName, $this->CDATAEncoding) && $this->version == Feed::ATOM) ? ' type="html"' : '';
        $nodeText .= "<{$tagName}{$attrText}>";
        $nodeText .= (in_array($tagName, $this->CDATAEncoding)) ? '<![CDATA[' : '';

        if (is_array($tagContent)) {
            foreach ($tagContent as $key => $value) {
                if (is_array($value)) {
                    $nodeText .= PHP_EOL;
                    foreach ($value as $subValue) {
                        $nodeText .= $this->makeNode($key, $subValue);
                    }
                } else if (is_string($value)) {
                    $nodeText .= $this->makeNode($key, $value);
                } else {
                    throw new \InvalidArgumentException("Unknown node-value type for $key");
                }
            }
        } else {
            $tagContent = self::filterInvalidXMLChars($tagContent);
            $nodeText .= (in_array($tagName, $this->CDATAEncoding)) ? $this->sanitizeCDATA($tagContent) : htmlspecialchars($tagContent);
        }

        $nodeText .= (in_array($tagName, $this->CDATAEncoding)) ? ']]>' : '';

        if (!$omitEndTag)
            $nodeText .= "</$tagName>";

        $nodeText .= PHP_EOL;

        return $nodeText;
    }

    /**
    * Make the channels.
    *
    * @access   private
    * @return   string  The feed header as XML containing all the feed metadata.
    */
    private function makeChannels()
    {
        $out = '';

        //Start channel tag
        switch ($this->version) {
            case Feed::RSS2:
                $out .= '<channel>' . PHP_EOL;
                break;
            case Feed::RSS1:
                $out .= (isset($this->data['ChannelAbout']))? "<channel rdf:about=\"{$this->data['ChannelAbout']}\">" : "<channel rdf:about=\"{$this->channels['link']['content']}\">";
                break;
        }

        //Print Items of channel
        foreach ($this->channels as $key => $value) {
            // In ATOM feeds, strip all ATOM namespace prefixes from the tag name. They are not needed here,
            // because the ATOM namespace name is set as default namespace.
            if ($this->version == Feed::ATOM && strncmp($key, 'atom', 4) == 0) {
                $key = substr($key, 5);
            }

            // The channel element can occur multiple times, when the key 'content' is not in the array.
            if (!array_key_exists('content', $value)) {
                // If this is the case, iterate through the array with the multiple elements.
                foreach ($value as $singleElement) {
                    $out .= $this->makeNode($key, $singleElement['content'], $singleElement['attributes']);
                }
            } else {
                $out .= $this->makeNode($key, $value['content'], $value['attributes']);
            }
        }

        if ($this->version == Feed::RSS1) {
            //RSS 1.0 have special tag <rdf:Seq> with channel
            $out .= "<items>" . PHP_EOL . "<rdf:Seq>" . PHP_EOL;
            foreach ($this->items as $item) {
                $thisItems = $item->getElements();
                $out .= "<rdf:li resource=\"{$thisItems['link']['content']}\"/>" . PHP_EOL;
            }
            $out .= "</rdf:Seq>" . PHP_EOL . "</items>" . PHP_EOL . "</channel>" . PHP_EOL;

            // An image has its own element after the channel elements.
            if (array_key_exists('image', $this->data))
                $out .= $this->makeNode('image', $this->data['Image'], array('rdf:about' => $this->data['Image']['url']));
        } else if ($this->version == Feed::ATOM) {
            // ATOM feeds have a unique feed ID. Use the title channel element as key.
            $out .= $this->makeNode('id', Feed::uuid($this->channels['title']['content'], 'urn:uuid:'));
        }

        return $out;
    }

    /**
    * Prints formatted feed items
    *
    * @access   private
    * @return   string  The XML of every feed item.
    */
    private function makeItems()
    {
        $out = '';

        foreach ($this->items as $item) {
            $thisItems = $item->getElements();

            // The argument is printed as rdf:about attribute of item in RSS 1.0
            // We're using the link set in the item (which is mandatory) as the about attribute.
            if ($this->version == Feed::RSS1)
                $out .= $this->startItem($thisItems['link']['content']);
            else
                $out .= $this->startItem();

            foreach ($thisItems as $feedItem) {
                $name = $feedItem['name'];

                // Strip all ATOM namespace prefixes from tags when feed is an ATOM feed.
                // Not needed here, because the ATOM namespace name is used as default namespace.
                if ($this->version == Feed::ATOM && strncmp($name, 'atom', 4) == 0)
                    $name = substr($name, 5);

                $out .= $this->makeNode($name, $feedItem['content'], $feedItem['attributes']);
            }
            $out .= $this->endItem();
        }

        return $out;
    }

    /**
    * Make the starting tag of channels
    *
    * @access   private
    * @param    string $about The value of about attribute which is used for RSS 1.0 only.
    * @return   string        The starting XML tag of an feed item.
    * @throws   InvalidOperationException if this object misses the data for the about attribute.
    */
    private function startItem($about = false)
    {
        $out = '';

        if ($this->version == Feed::RSS2) {
            $out .= '<item>' . PHP_EOL;
        } elseif ($this->version == Feed::RSS1) {
            if ($about) {
                $out .= "<item rdf:about=\"$about\">" . PHP_EOL;
            } else {
                throw new InvalidOperationException("Missing data for about attribute. Call setChannelAbout method.");
            }
        } elseif ($this->version == Feed::ATOM) {
            $out .= "<entry>" . PHP_EOL;
        }

        return $out;
    }

    /**
    * Closes feed item tag
    *
    * @access   private
    * @return   string  The ending XML tag of an feed item.
    */
    private function endItem()
    {
        if ($this->version == Feed::RSS2 || $this->version == Feed::RSS1) {
            return '</item>' . PHP_EOL;
        } elseif ($this->version == Feed::ATOM) {
            return '</entry>' . PHP_EOL;
        }
    }

    /**
    * Sanitizes data which will be later on returned as CDATA in the feed.
    *
    * A "]]>" respectively "<![CDATA" in the data would break the CDATA in the
    * XML, so the brackets are converted to a HTML entity.
    *
    * @access   private
    * @param    string $text Data to be sanitized
    * @return   string  Sanitized data
    */
    private function sanitizeCDATA($text)
    {
        $text = str_replace("]]>", "]]&gt;", $text);
        $text = str_replace("<![CDATA[", "&lt;![CDATA[", $text);

        return $text;
    }

    // End # private functions ----------------------------------------------

} // end of class Feed


/**
 * Wrapper for creating RSS2 feeds
 *
 * @package     UniversalFeedWriter
 */
class RSS2 extends Feed
{
    /**
    * {@inheritdoc}
    */
    public function __construct()
    {
        parent::__construct(Feed::RSS2);
    }
}


/**
 * The exception that is thrown when an invalid operation is performed on
 * the object.
 *
 * @package     UniversalFeedWriter
 */
class InvalidOperationException extends LogicException
{
}



date_default_timezone_set('America/New_York');
//use \FeedWriter\RSS2;