<?php
/**
 * YAWIK-SimpleImport
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2019 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace SimpleImport\Queue;

use Core\Queue\Exception\FatalJobException;
use Core\Queue\Exception\RecoverableJobException;
use Jobs\Repository\Job;
use SimpleImport\Service\LanguageGuesser;
use SlmQueue\Job\AbstractJob;
use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerAwareTrait;

/**
 * ${CARET}
 * 
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test 
 */
class GuessLanguageJob extends AbstractJob implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $repository;
    private $guesser;
    private $jobId;
    protected $content = [
        'jobId' => null,
    ];

    public function __construct(Job $repository, LanguageGuesser $guesser)
    {
        $this->repository = $repository;
        $this->guesser = $guesser;
    }

    public function execute()
    {
        /* @var \Jobs\Entity\Job $job */
        $logger = $this->getLogger();
        $jobId = $this->getJobId();
        $job   = $this->repository->find($jobId);

        if (!$job) {
            $logger->err('A job with the id "' . $jobId . '" does not exist.');
            throw new FatalJobException('A job with the id "' . $jobId . '" does not exist."');
        }

        if ($job->getLanguage()) {
            $logger->info('Job has a language set already. [ jobId: ' . $jobId . ']');
            return;
        }

        $templValues = $job->getTemplateValues();
        $textParts   = [
            $templValues->getTitle(),
            $templValues->getBenefits(),
            $templValues->getDescription(),
            $templValues->getHtml(),
            $templValues->getQualifications(),
            $templValues->getRequirements(),
            $job->getTitle(),
        ];
        $textParts = array_map('strip_tags', $textParts);
        $text      = implode(' ', $textParts);

        $logger->debug('Text: ' . $text);
        $result = $this->guesser->guess($text);

        if (!$result['ok']) {
            $message = <<<MESSAGE
Could not detect language for job "$jobId"

                Used text: 
                ----------
                $text
                
                
                Language Guesser Result
                -----------------------
                %s
                
MESSAGE;


            $logger->err(sprintf($message, var_export($result['error'], true)));
            $logger->notice('Will retry in 30 seconds.');
            throw new RecoverableJobException('Could not detect language for job "' . $jobId . '"', ['delay' => 30]);
        }

        $job->setLanguage($result['language']);
        $this->repository->store($job);

        $logger->info(sprintf('Set language to "%s" for job "%s"', $result['language'], $jobId));
        return;
    }

    public function setJobId($jobId)
    {
        $this->content['jobId'] = $jobId;
    }

    public function getJobId()
    {
        return $this->content['jobId'];
    }


    public function setContent($value)
    {
        if (!is_array($value) && !$value instanceOf \Traversable) {
            throw new \InvalidArgumentException('Payload must be an array or \Traversable');
        }

        // assure needed keys are present
        $value = array_merge(
            $this->content,
            $value
        );

        return parent::setContent($value);
    }
}
