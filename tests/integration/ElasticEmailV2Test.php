<?php
/**
 * @author Rizart Dokollari <r.dokollari@gmail.com>
 * @since 7/30/16
 */

namespace Tests\integration;

use ElasticEmail\V2\Requests\Email\RequestException;
use ElasticEmail\V2\Responses\Email\EmailResponse;
use Tests\TestCase;
use VCR\Request;

class ElasticEmailV2Test extends TestCase
{
    /**
     * @test
     * @vcr sends_a_successful_email.yml
     */
    public function sends_a_successful_email()
    {
        $response = $this->sendSuccessfulEmail();

        $this->assertInstanceOf(EmailResponse::class, $response);

        $this->assertTrue($response->wasSuccessful());

        $this->assertNull($response->getErrorMessage());

        $this->assertRegExp('/[a-z]+/', $response->getData());
        $this->assertRegExp('/[A-Z]+/', $response->getData());
        $this->assertRegExp('/[0-9]+/', $response->getData());
        $this->assertRegExp('/-/', $response->getData());
        $this->assertRegExp('/transactionid/', $response->getData());
        $this->assertRegExp('/messageid/', $response->getData());

        $this->assertRegExp('/^[0-9a-z-]+$/', $response->getTransactionId());

        $this->assertRegExp('/[a-z]+/', $response->getMessageId());
        $this->assertRegExp('/[A-Z]+/', $response->getMessageId());
        $this->assertRegExp('/[0-9]+/', $response->getMessageId());
        
    }


    /**
     * @test
     * @vcr sends_an_email.yml
     */
    public function throws_exception_if_no_recipient_is_set()
    {
        $this->setExpectedException(RequestException::class, "At least one recipient must be specified. Array key: 'to'");

        $this->elasticEmail->email()->send([]);
    }

    /**
     * @test
     * @vcr throws_exception_if_no_recipient_email_is_invalid.yml
     * @dataProvider invalidEmailsProvider
     * @param $invalidEmail
     */
    public function throws_exception_if_no_recipient_email_is_invalid($invalidEmail)
    {
        $this->setExpectedException(RequestException::class, 'Invalid recipient email.');

        $this->elasticEmail->email()->send(['to' => $invalidEmail]);
    }

    public function invalidEmailsProvider()
    {
        return [
            ['invalid'],
            ['example.com1'],
            ['.@example.com1'],
        ];
    }

    /**
     * @test
     * @vcr throws_exception_if_no_subject_is_specified.yml
     */
    public function throws_exception_if_no_subject_is_specified()
    {
        $this->setExpectedException(RequestException::class, 'Subject field must be specified.');

        $this->elasticEmail->email()->send(['to' => getenv('SINGLE_TESTER_EMAIL')]);
    }

    /**
     * @test
     * @vcr throws_exception_if_no_send_email_is_specified.yml
     */
    public function throws_exception_if_no_send_email_is_specified()
    {
        $this->setExpectedException(RequestException::class, 'Invalid FROM email address.');

        $this->elasticEmail->email()->send([
            'to'      => getenv('SINGLE_TESTER_EMAIL'),
            'subject' => getenv('EMAIL_SUBJECT')
        ]);
    }
}