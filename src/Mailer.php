<?php namespace Vanchelo\Mailer;

use Closure;
use Swift_Mailer;
use Swift_Message;
use Phalcon\Mvc\View\Simple as View;

class Mailer {

    /**
     * The view environment instance.
     *
     * @var \Phalcon\Mvc\View\Simple
     */
    protected $view;

    /**
     * The Swift Mailer instance.
     *
     * @var Swift_Mailer
     */
    protected $swift;

    /**
     * The global from address and name.
     *
     * @var array
     */
    protected $from;

    /**
     * Create a new Mailer instance.
     *
     * @param  \Phalcon\Mvc\View\Simple $views
     * @param  Swift_Mailer  $swift
     * @return void
     */
    public function __construct(View $view, Swift_Mailer $swift)
    {
        $this->view = $view;
        $this->swift = $swift;
    }

    /**
     * Set the global from address and name.
     *
     * @param  string  $address
     * @param  string  $name
     * @return void
     */
    public function alwaysFrom($address, $name = null)
    {
        $this->from = compact('address', 'name');
    }

    /**
     * Send a new message when only a plain part.
     *
     * @param  string  $view
     * @param  array   $data
     * @param  mixed  $callback
     * @return integer
     */
    public function plain($view, array $data, $callback)
    {
        return $this->send(array('text' => $view), $data, $callback);
    }

    /**
     * Send a new message using a view.
     *
     * @param  string|array  $view
     * @param  array  $data
     * @param  Closure|string  $callback
     * @return integer
     */
    public function send($view, array $data, $callback)
    {
        // First we need to parse the view, which could either be a string or an array
        // containing both an HTML and plain text versions of the view which should
        // be used when sending an e-mail. We will extract both of them out here.
        list($view, $plain) = $this->parseView($view);

        $data['message'] = $message = $this->createMessage();

        $this->callMessageBuilder($callback, $message);

        // Once we have retrieved the view content for the e-mail we will set the body
        // of this message using the HTML type, which will provide a simple wrapper
        // to creating view based emails that are able to receive arrays of data.
        $this->addContent($message, $view, $plain, $data);

        $message = $message->getSwiftMessage();

        return $this->sendSwiftMessage($message);
    }

    /**
     * Add the content to a given message.
     *
     * @param  Message  $message
     * @param  string  $view
     * @param  string  $plain
     * @param  array   $data
     * @return void
     */
    protected function addContent($message, $view, $plain, $data)
    {
        if (isset($view))
        {
            $message->setBody($this->getView($view, $data), 'text/html');
        }

        if (isset($plain))
        {
            $message->addPart($this->getView($plain, $data), 'text/plain');
        }
    }

    /**
     * Parse the given view name or array.
     *
     * @param  string|array  $view
     * @return array
     */
    protected function parseView($view)
    {
        if (is_string($view)) return array($view, null);

        // If the given view is an array with numeric keys, we will just assume that
        // both a "pretty" and "plain" view were provided, so we will return this
        // array as is, since must should contain both views with numeric keys.
        if (is_array($view) and isset($view[0]))
        {
            return $view;
        }

        // If the view is an array, but doesn't contain numeric keys, we will assume
        // the the views are being explicitly specified and will extract them via
        // named keys instead, allowing the developers to use one or the other.
        elseif (is_array($view))
        {
            return array(
                array_get($view, 'html'), array_get($view, 'text')
            );
        }

        throw new \InvalidArgumentException("Invalid view.");
    }

    /**
     * Send a Swift Message instance.
     *
     * @param  Swift_Message  $message
     * @return integer
     */
    protected function sendSwiftMessage($message)
    {
        return $this->swift->send($message);
    }

    /**
     * Call the provided message builder.
     *
     * @param  Closure|string  $callback
     * @param  Message  $message
     * @return void
     */
    protected function callMessageBuilder($callback, $message)
    {
        if ($callback instanceof Closure)
        {
            return call_user_func($callback, $message);
        }

        throw new \InvalidArgumentException("Callback is not valid.");
    }

    /**
     * Create a new message instance.
     *
     * @return Message
     */
    protected function createMessage()
    {
        $message = new Message(new Swift_Message);

        // If a global from address has been specified we will set it on every message
        // instances so the developer does not have to repeat themselves every time
        // they create a new message. We will just go ahead and push the address.
        if (isset($this->from['address']))
        {
            $message->from($this->from['address'], $this->from['name']);
        }

        return $message;
    }

    /**
     * Render the given view.
     *
     * @param  string  $view
     * @param  array   $data
     * @return string
     */
    protected function getView($template, $data)
    {
        return $this->view->render($template, $data);
    }

    /**
     * Get the view environment instance.
     *
     * @return \Phalcon\Mvc\View
     */
    public function getViewEnvironment()
    {
        return $this->view;
    }

    /**
     * Get the Swift Mailer instance.
     *
     * @return Swift_Mailer
     */
    public function getSwiftMailer()
    {
        return $this->swift;
    }

    /**
     * Set the Swift Mailer instance.
     *
     * @param  Swift_Mailer  $swift
     * @return void
     */
    public function setSwiftMailer($swift)
    {
        $this->swift = $swift;
    }

}


function array_get($array, $key, $default = null)
{
    if (is_null($key)) return $array;

    if (isset($array[$key])) return $array[$key];

    foreach (explode('.', $key) as $segment)
    {
        if ( ! is_array($array) or ! array_key_exists($segment, $array))
        {
            return value($default);
        }

        $array = $array[$segment];
    }

    return $array;
}

function value($value)
{
    return $value instanceof Closure ? $value() : $value;
}