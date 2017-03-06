<?php
namespace IchHabRecht\GitWrapper;

class GitRepository
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * @var GitWrapper
     */
    protected $wrapper;

    /**
     * @var string
     */
    protected $__lastResult;

    /**
     * @param GitWrapper $wrapper
     * @param string $directory
     */
    public function __construct(GitWrapper $wrapper, $directory)
    {
        $this->wrapper = $wrapper;
        $this->directory = $directory;
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @param string $cloneUrl
     * @param array $options
     * @param array $arguments
     * @return string
     */
    public function create($cloneUrl, array $options = [], array $arguments = [])
    {
        $arguments[] = $cloneUrl;
        $arguments[] = basename($this->directory);
        $this->__lastResult = $this->wrapper->execute('clone', $options, $arguments, dirname($this->directory), null);

        return trim($this->__lastResult);
    }

    /**
     * @param array $options
     * @param array $arguments
     * @return string
     */
    public function branch(array $options = [], array $arguments = [])
    {
        $this->__lastResult = $this->wrapper->execute('branch', $options, $arguments, $this->directory, null);

        return $this->splitOutput($this->__lastResult);
    }

    /**
     * @param array $options
     * @param array $arguments
     * @return string
     */
    public function checkout(array $options = [], array $arguments = [])
    {
        $this->__lastResult = $this->wrapper->execute('checkout', $options, $arguments, $this->directory, null);

        return trim($this->__lastResult);
    }

    /**
     * @param array $options
     * @param array $arguments
     * @return string
     */
    public function fetch(array $options = [], array $arguments = [])
    {
        $this->__lastResult = $this->wrapper->execute('fetch', $options, $arguments, $this->directory, null);

        return trim($this->__lastResult);
    }

    /**
     * @return string
     */
    public function getCurrentBranch()
    {
        $this->__lastResult = $this->wrapper->execute('rev-parse', ['abbrev-ref'], ['HEAD'], $this->directory);

        return trim($this->__lastResult);
    }

    /**
     * @return array
     */
    public function getTrackingInformation()
    {
        if (!$this->hasTrackingBranch()) {
            $branch = substr($this->__lastResult, 3);

            return [
                'branch' => $branch,
                'remoteBranch' => '',
                'ahead' => 0,
                'behind' => 0,
            ];
        }

        $trackingParts = explode('...', $this->__lastResult, 2);
        $branch = substr($trackingParts[0], 3);
        $trackingParts = explode(' ', $trackingParts[1], 2);
        $remoteBranch = $trackingParts[0];
        if (!empty($trackingParts[1])) {
            preg_match('{\[(?:ahead (\d+)(?:, )?)?(?:behind (\d+))?\]}', $trackingParts[1], $match);
        }

        return [
            'branch' => $branch,
            'remoteBranch' => $remoteBranch,
            'ahead' => isset($match[1]) ? (int)$match[1] : 0,
            'behind' => isset($match[2]) ? (int)$match[2] : 0,
        ];
    }

    /**
     * @return bool
     */
    public function hasTrackingBranch()
    {
        $output = $this->splitOutput($this->wrapper->execute('status', ['s', 'b'], [], $this->directory));
        $this->__lastResult = $output[0];

        return strpos($this->__lastResult, '...') !== false;
    }

    /**
     * @param array $options
     * @param array $arguments
     * @return string
     */
    public function log(array $options = [], array $arguments = [])
    {
        $this->__lastResult = $this->wrapper->execute('log', $options, $arguments, $this->directory, null);

        return trim($this->__lastResult);
    }

    /**
     * @param array $options
     * @param array $arguments
     * @return string
     */
    public function pull(array $options = [], array $arguments = [])
    {
        $this->__lastResult = $this->wrapper->execute('pull', $options, $arguments, $this->directory, null);

        return trim($this->__lastResult);
    }

    /**
     * @param array $options
     * @param array $arguments
     * @return string
     */
    public function reset(array $options = [], array $arguments = [])
    {
        $this->__lastResult = $this->wrapper->execute('reset', $options, $arguments, $this->directory, null);

        return trim($this->__lastResult);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        $this->__lastResult = $this->wrapper->execute('status', ['s'], [], $this->directory);

        return $this->__lastResult;
    }

    /**
     * @return bool
     */
    public function hasChanges()
    {
        return !empty($this->getStatus());
    }

    /**
     * @param string $output
     * @return array
     */
    protected function splitOutput($output)
    {
        return array_filter(array_map('trim', preg_split('/\\r\\n|\\r|\\n/', $output)));
    }
}
