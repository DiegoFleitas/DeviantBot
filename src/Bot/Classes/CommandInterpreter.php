<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/26/2019
 * Time: 5:11 PM
 */

use Stringy\Stringy as S;

class CommandInterpreter extends DataLogger
{

    // inclusive
    protected $maxlength = 80;
    protected $minlength = 5;
    protected $maxwords = 4;
    protected $minwords = 2;
    protected $separator = ' ';
    protected $available_commands = array(
        'keyword',
        'tag'
    );

    /**
     * @return int
     */
    public function getMinlength()
    {
        return $this->minlength;
    }


    /**
     * @return int
     */
    public function getMaxlength()
    {
        return $this->maxlength;
    }


    /**
     * @return int
     */
    public function getMaxwords()
    {
        return $this->maxwords;
    }

    /**
     * @return int
     */
    public function getMinwords()
    {
        return $this->minwords;
    }

    /**
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * @return array
     */
    public function getAvailableCommands()
    {
        return $this->available_commands;
    }


    /**
     * @param string $_COMMENT
     * @return array
     */
    public function identifyCommand($_COMMENT)
    {

        $length = strlen($_COMMENT);
        $comment = S::create($_COMMENT);
        if ($length <= $this->getMaxlength() && $length > $this->getMinlength()) {
            try {
                $message = 'identifying [' . $_COMMENT . ']';
                $this->logdata($message);

                // let silly people use it too
                $comment = $comment->trim();
                $comment = $comment->collapseWhitespace();
                // Might need to change this to accept $comment->isAlphanumeric() too in the future
                $non_space = str_replace(' ', '', $comment);
                $alpha = S::create($non_space)->isAlpha();
                if ($alpha) {
                    // Returns an array with a maximum of maxwords elements
                    $tokens = explode($this->getSeparator(), $comment, $this->getMaxwords());
                    if (count($tokens) >= $this->getMinwords()) {
                        $command = $this->isCommand($tokens[0]);
                        if ($command) {
                            $result = $this->validateCommand($tokens);
                            if ($result['success']) {
                                $message = 'identified command [' . $result['command'] . '] params [' .  implode(' ', $result['params']) . ']';
                                $this->logdata($message);

                                return array(
                                    'command' => $result['command'],
                                    'params' => $result['params'],
                                );
                            } else {
                                $message = 'invalid command. '.$result['reason'];
                                $this->logdata($message);
                            }
                        } else {
                            $message = 'command does not exist';
                            $this->logdata($message);
                        }
                    } else {
                        $message = 'command unrecognized';
                        $this->logdata($message);
                    }
                } else {
                    // comment contained characters that are not addmited
                    $message = 'command is not well formatted';
                    $this->logdata($message);
                }

                return array(
                    'command' => $result['command'],
                    'params' => $result['params'],
                    'output' => $message
                );
            } catch (Exception $e) {
                $data = $e->getMessage();
                $this->logdata($data, 1);
            }
        } else {
            $comment->truncate($this->getMaxlength(), '...');
            $message = 'Too long/short. Comment: '.$comment;
            $this->logdata($message);
        }
    }

    /**
     * @param string $word
     * @return bool|mixed
     */
    public function isCommand($word)
    {

        try {
            $available = $this->getAvailableCommands();
            $key = array_search($word, $available);
            // 0 is a key you know
            if ($key !== false) {
                return $available[$key];
            }
            return false;
        } catch (Exception $e) {
            $data = $e->getMessage();
            $this->logdata($data, 1);
        }
    }

    /**
     * @param array $tokens
     * @return array
     */
    public function validateCommand($tokens)
    {

        try {
            $result = array(
                'command' => 'none',
                'params' => array(),
                'success' => false,
                'reason' => '',
            );
            $command = $tokens[0];
            $params = array_slice($tokens, 1);
            switch ($command) {
                case 'keyword':
                    /** @var array $result */
                    $result['command'] = 'keyword';
                    if (count($params) > 3) {
                        $result['reason'] = 'too many params';
                        break;
                    }
                    foreach ($params as $index => $param) {
                        if (strlen($param) > 20) {
                            $result['reason'] = 'param ' . ($index + 1) . ' too long';
                            break;
                        }
                    }
                    $result['params'] = $params;
                    $result['success'] = 'true';
                    break;

                case 'tag':
                    $result['command'] = 'tag';
                    if (count($params) > 3) {
                        $result['reason'] = 'too many params';
                        break;
                    }
                    foreach ($params as $index => $param) {
                        if (strlen($param) > 20) {
                            $result['reason'] = 'param ' . ($index + 1) . ' too long';
                            break;
                        }
                    }
                    $result['params'] = $params;
                    $result['success'] = 'true';
                    break;
            }
            return $result;
        } catch (Exception $e) {
            $data = $e->getMessage();
            $this->logdata($data, 1);
        }
    }
}
