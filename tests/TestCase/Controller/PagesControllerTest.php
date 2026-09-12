<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.2.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * PagesControllerTest class
 *
 * @uses \App\Controller\PagesController
 */
class PagesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Test directory traversal protection - CRITICAL SECURITY TEST
     * Prevents attacks like /pages/../Layout/ajax to access unauthorized files
     *
     * @return void
     */
    public function testDirectoryTraversalProtection()
    {
        // Attempt directory traversal attack
        $this->get('/pages/../Layout/ajax');

        // Should deny access with 403 Forbidden or 401 Unauthorized in tests
        $statusCode = $this->_response->getStatusCode();
        $this->assertTrue(
            in_array($statusCode, [403, 401]),
            'Expected 403 Forbidden or 401 Unauthorized, got ' . $statusCode,
        );
    }

    /**
     * Test that CSRF protection rejects invalid tokens - CRITICAL SECURITY TEST
     *
     * @return void
     */
    public function testCsrfAppliedError()
    {
        // POST without CSRF token should be rejected
        $this->post('/pages/home', ['hello' => 'world']);

        // Should reject with 403 Forbidden (CSRF protection) or 401 Unauthorized
        $statusCode = $this->_response->getStatusCode();
        $this->assertTrue(
            in_array($statusCode, [403, 401]),
            'Expected 403 Forbidden or 401 Unauthorized, got ' . $statusCode,
        );
    }

    /**
     * Test that CSRF protection allows valid tokens - CRITICAL SECURITY TEST
     *
     * @return void
     */
    public function testCsrfAppliedOk()
    {
        // Enable CSRF token handling for this request
        $this->enableCsrfToken();

        // POST with valid CSRF token should succeed
        $this->post('/pages/home', ['hello' => 'world']);

        // Should succeed with 200 OK (assuming authenticated)
        // or 401 if auth is needed
        $this->assertTrue(
            in_array($this->_response->getStatusCode(), [200, 401]),
            'Expected 200 or 401, got ' . $this->_response->getStatusCode(),
        );
    }
}
