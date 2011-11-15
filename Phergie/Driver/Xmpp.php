<?php
/**
 * Phergie
 *
 * PHP version 5
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://phergie.org/license
 *
 * @category  Phergie
 * @package   Phergie_Driver_Xmpp
 * @author    Phergie Development Team <team@phergie.org>
 * @copyright 2008-2010 Phergie Development Team (http://phergie.org)
 * @license   http://phergie.org/license New BSD License
 * @link      http://pear.phergie.org/package/Phergie_Driver_Xmpp
 */

require 'Xmpp/Connection.php';

/**
 * Driver that connects to an XMPP server rather than IRC, using an external
 * XMPP library.
 *
 * @category Phergie
 * @package  Phergie_Driver_Xmpp
 * @author   Phergie Development Team <team@phergie.org>
 * @license  http://phergie.org/license New BSD License
 * @link     http://pear.phergie.org/package/Phergie_Driver_Xmpp
 */
class Phergie_Driver_Xmpp extends Phergie_Driver_Abstract
{

	/**
	 * Set whether or not a MOTD event has been faked yet.
	 * 
	 * @var boolean 
	 */
	protected $fakedMotd = false;

	/**
	 * Holds the connection to the XMPP server.
	 * 
	 * @var Xmpp_Connection 
	 */
	protected $xmpp;

	/**
	 * Establishes an XMPP connection.
	 * 
	 * This is done in a seperate function mainly to allow for stubbing during
	 * unit testing.
	 * 
	 * @param string $username Username to authenticate with.
	 * @param string $password Password associated with the username.
	 * @param string $hostname Host name to connect to.
	 * @param bool   $ssl      Whether or not to use SSL.
	 * @param int    $port     Port to connect to the server on.
	 * @param string $resource "Resource" part of the JID to connect with.
	 * 
	 * @return Xmpp_Connection
	 */
	protected function connect($username, $password, $hostname, $ssl, $port, $resource = 'Bot')
	{
		return new Xmpp_Connection(
			$username, $password, $hostname, $ssl, Zend_Log::EMERG, $port,
			$resource);
	}

    /**
     * There isn't actually an XMPP equivilent to the IRC ACTION command, but
	 * most clients with interpret a message starting "/me" in the same way,
	 * so we'll just prepend that onto the text.
     *
     * @param string $target MUC name or user nick
     * @param string $text   Text of the action to perform
     *
     * @return void
     */
    public function doAction($target, $text)
    {
		$this->doPrivmsg($target, '/me ' . $text);
    }

    /**
     * Initiates a connection with the server.
     *
     * @return void
     */
    public function doConnect()
    {
		// Listen for input indefinitely
        set_time_limit(0);

        // Get connection information
        $connection = $this->getConnection();
        $hostname = $connection->getHost();
        $port = $connection->getPort();
        $password = $connection->getPassword();
        $username = $connection->getUsername();
        $nick = $connection->getNick();
        $realname = $connection->getRealname();
        $transport = $connection->getTransport();

		// Always default to SSL unless tcp is explicitly asked for.
		if ($transport == 'tcp') {
			$ssl = false;
		} else {
			$ssl = true;
		}

		$this->xmpp = $this->connect($username, $password, $hostname, $ssl, $port);

        if (!$this->xmpp) {
            throw new Phergie_Driver_Exception(
                'Unable to connect.',
                Phergie_Driver_Exception::ERR_CONNECTION_ATTEMPT_FAILED
            );
        }

		$this->xmpp->connect();
		$this->xmpp->authenticate();
		$this->xmpp->bind();
		$this->xmpp->establishSession();
		$this->xmpp->presence();
	}

    /**
     * There does not appear to be an XMPP equivilent for this command, so it
	 * will be left unimplemented.
     *
     * @param string $nick User nick
     * @param string $finger Finger string to send for a response
     *
     * @return void
     */
    public function doFinger($nick, $finger = null)
    {
    }

    /**
     * Invites a user to an invite-only MUC.
     *
     * @param string $nick Nick of the user to invite
     * @param string $muc  Address of the multi-user chat.
     *
     * @return void
     */
    public function doInvite($nick, $muc)
    { 
    }

    /**
     * Joins a MUC.
     *
     * @param string $mucs Comma-delimited list of mucs to join
     * @param string $keys Optional comma-delimited list of muc keys. Not in
	 *                     use in this driver.
     *
     * @return void
     */
    public function doJoin($mucs, $keys = null)
	{
		// Explode the list on the comma and join all of the channels specified
		$mucs = explode(',', $mucs);
		
		foreach ($mucs as $muc) {
			$this->xmpp->join($muc, $this->getConnection()->getNick(), true);
		}
	}

    /**
     * Kicks a user from a MUC.
     *
     * @param string $nick   Nick of the user
     * @param string $muc    MUC address
     * @param string $reason Reason for the kick (optional)
     *
     * @return void
     */
    public function doKick($nick, $muc, $reason = null)
    {
    }

    /**
     * Obtains a list of MUCs available.
     *
     * @param string $mucs Comma-delimited list of one or more mucs to which
	 *                     the response should be restricted (optional)
     *
     * @return void
     */
    public function doList($mucs = null)
    {
    }

    /**
     * Retrieves or changes a MUC or user mode.
     *
     * @param string $target Channel name or user nick
     * @param string $mode   New mode to assign (optional)
     * @param string $param  User limit when $mode is 'l', user hostmask
     *        when $mode is 'b', or user nick when $mode is 'o'
     *
     * @return void
     */
    public function doMode($target, $mode = null, $param = null)
    {
    }

    /**
     * Obtains a list of nicks of usrs in currently joined MUCs.
     *
     * @param string $mucs Comma-delimited list of one or more mucs
     *
     * @return void
     */
    public function doNames($mucs)
    {
    }

    /**
     * Changes the client nick.
     *
     * @param string $nick New nick to assign
     *
     * @return void
     */
    public function doNick($nick)
    {
	}

    /**
     * Sends a notice to a nick or MUC.
     *
     * @param string $target MUC name or user nick
     * @param string $text   Text of the notice to send
     *
     * @return void
     */
    public function doNotice($target, $text)
    {
		$this->doPrivmsg($target, $text);
    }

    /**
     * Leaves a MUC.
     *
     * @param string $mucs Comma-delimited list of MUCs to leave
     *
     * @return void
     */
    public function doPart($mucs)
    {
    }

    /**
     * Sends a <ping/> tag to the server. The nick and and hash are ignored on 
	 * XMPP.
     *
     * @param string $nick User nick
     * @param string $hash Hash to use in the handshake
     *
     * @return void
     */
    public function doPing($nick, $hash)
    {
		$this->xmpp->ping();
    }

    /**
     * Responds to a server test of client responsiveness.
     *
     * @param string $daemon Daemon from which the original request originates
     *
     * @return void
     */
    public function doPong($daemon)
    {
    }

    /**
     * Sends a message to a nick or MUC.
     *
     * @param string $target MUC name or user nick
     * @param string $text   Text of the message to send
     *
     * @return void
     */
    public function doPrivmsg($target, $text)
    {
		$this->xmpp->message($target, $text);
    }

    /**
     * Terminates the connection with the server.
     *
     * @param string $reason Reason for connection termination (optional)
     *
     * @return void
     */
    public function doQuit($reason = null)
    {
		// We don't pass the reason on here because that is used for IRC
		// connections, it is irrelevant for XMPP connections.
		$this->xmpp->disconnect();
	}

    /**
     * Sends a raw command to the server.
     *
     * @param string $command Command string to send
     *
     * @return void
     */
    public function doRaw($command)
    {
    }

    /**
     * Sends a CTCP TIME request to a user.
     *
     * @param string $nick User nick
     * @param string $time Time string to send for a response
     *
     * @return void
     */
    public function doTime($nick, $time = null)
    {
    }

    /**
     * Retrieves or changes a muc topic.
     *
     * @param string $muc Name of the muc
     * @param string $topic   New topic to assign (optional)
     *
     * @return void
     */
    public function doTopic($muc, $topic = null)
    {
    }

    /**
     * Sends a CTCP VERSION request or response to a user.
     *
     * @param string $nick User nick
     * @param string $version Version string to send for a response
     *
     * @return void
     */
    public function doVersion($nick, $version = null)
    {
    }

    /**
     * Retrieves information about a nick.
     *
     * @param string $nick Nick
     *
     * @return void
     */
    public function doWhois($nick)
    {
    }

    /**
     * Listens for an event on the current connection.
     *
     * @return Phergie_Event_Interface|null Event instance if an event was
     *         received, NULL otherwise
     */
    public function getEvent()
    {

		// If a MOTD has not yet been faked, do it now
		if ($this->fakedMotd == false) {

			// XMPP does not require an MOTD the same way that IRC does, so we need
			// to fake a no motd error to trigger any plugins that depend on the
			// MOTD.
			$event = new Phergie_Event_Response();
			$event->setCode(Phergie_Event_Response::ERR_NOMOTD)->setDescription('');
			$this->fakedMotd = true;

		} else {

			$tag = $this->xmpp->wait();

			// If there is no tag that means we received nothing from the server
			// and no event has occured.
			if (empty($tag)) {
				return null;
			}
			
			// Holding array for the arguments.
			$args = array();

			// Format the arguments as required for the command that was
			// received
			switch ($tag) {
				case 'message':
					$stanza = $this->xmpp->getMessage();
					$from = $stanza->getFrom();
					$bodies = $stanza->getBodies();

					if (count($bodies) > 0) {
						$cmd = 'privmsg';
						/**
						 * @todo There may be more than one body. Should
						 *		 handle that situation.
						 */
						$args[] = $bodies[0]['content'];
					}
					
					// Prepend args with source of message so the plugins know
					// who to send the response to.
					
					// If it's a group chat message, we want to strip off the 
					// nickname so it doesn't decide that it's a normal message
					// later.
					if ($stanza->getType() == 'groupchat') {
						// Get it again because we still want the nickname in
						// there when processing the actual message...
						array_unshift($args, array_shift(explode('/', $stanza->getFrom())));
					} else {
						array_unshift($args, $from);
					}
					
					break;

				case 'presence':
					unset($cmd);
					break;
				
				case 'iq':
					$stanza = $this->xmpp->getIq();
					$from = $stanza->getFrom();
					$cmd = 'pong';
					break;

				default:
					break;
			}

			if (!isset($cmd)) {
				return null;
			}

			$hostmask = Phergie_Hostmask_Xmpp::fromString($from, $stanza->getType());
            
            // One difference between IRC and XMPP is that the server will return  
            // stanzas the client sends back to client again. For example, if a 
            // client sends a message to a chat room on the server, the server then
            // sends the message on to all of the people in the chat room, including
            // the original sender. Therefore if the message has come from Phergie
            // we want to stop her responding to her own messages. Most of the time
            // this is benign when it happens, but the Lart and Dice plugins can 
            // cause Phergie to get stuck in an infinite loop.
            if (($stanza->getType() == 'groupchat'  && $hostmask->getNick() == $this->getConnection()->getNick())
                    || $hostmask->getUsername() == $this->getConnection()->getUsername()) {
                return null;
            }

			$event = new Phergie_Event_Request_Xmpp;
			$event->setType($cmd)
				  ->setArguments($args)
				  ->setHostmask($hostmask);            

		}
        return $event;

	}

}