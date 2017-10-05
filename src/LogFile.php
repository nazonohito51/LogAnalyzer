<?php

namespace LogAnalyzer;

use Clover\Text\LTSV;
use Kassner\LogParser\LogParser;
use LogAnalyzer\Entries\Entry;

/**
 * ���ե�����1��ʬ����ݲ��������饹
 * ������¸������apache log�Ǥ�ltsv�Ǥ�ʤ�Ǥ��ɤ�
 * @package LogAnalyzer
 */
class LogFile
{
    /**
     * @var LogParser|LTSV
     */
    private $parser;

    private $file;
    private $log_type;
    private $options;

    public function __construct($path, array $options = [])
    {
        $this->file = new \SplFileObject($path);
        if (!$this->file->isFile()) {
            throw new \InvalidArgumentException();
        }

        $this->log_type = $this->file->getExtension() == 'ltsv' ? 'ltsv' : 'apache';
        if (isset($options['log_type'])) {
            $this->log_type = $options['log_type'];
        }
        $this->options = [
            'format' => isset($options['format']) ? $options['format'] : null
        ];

        if ($this->log_type == 'apache') {
            $this->parser = new LogParser($this->options['format']);
            // $parser->setFormat('%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\"');
        } elseif ($this->log_type == 'ltsv') {
            $this->parser = new LTSV();
        } else {
            throw new \InvalidArgumentException('log_type is invalid.');
        }
    }

    public function getEntry()
    {
        $line = !$this->file->eof() ? $this->file->fgets() : null;
        if (empty($line)) {
            return null;
        }

        if ($this->log_type == 'apache') {
            $iterable = $this->parser->parse($line);
        } elseif ($this->log_type == 'ltsv') {
            $iterable = $this->parser->parseLine($line);
        } else {
            throw new \LogicException('log_type is invalid.');
        }

        return new Entry($iterable);
    }
}
