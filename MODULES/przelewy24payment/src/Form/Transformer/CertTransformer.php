<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Przelewy24 powered by Waynet
 * @copyright Przelewy24
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace Przelewy24\Form\Transformer;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CertTransformer implements DataTransformerInterface
{
    private const ALLOWED_MIME_TYPES = [
        'application/x-pem-file',
        'application/x-x509-ca-cert',
        'text/plain',
    ];

    public function transform($value)
    {
        return null;
    }

    public function reverseTransform($value)
    {
        if (!empty($value) && $value instanceof UploadedFile) {
            if (!$value->isValid()) {
                throw new TransformationFailedException('Invalid file upload');
            }

            $extension = strtolower($value->getClientOriginalExtension());
            if ($extension !== 'pem') {
                throw new TransformationFailedException('File must have .pem extension');
            }

            $mimeType = $value->getMimeType();
            if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
                throw new TransformationFailedException('Invalid file type. Must be a PEM certificate');
            }

            $content = file_get_contents($value->getPathname());
            if ($content === false) {
                throw new TransformationFailedException('Could not read file content');
            }

            if (!$this->isPemContent($content)) {
                throw new TransformationFailedException('Invalid PEM file content');
            }

            return $content;
        }

        return null;
    }

    private function isPemContent(string $content): bool
    {
        $pemPattern = '/^-----BEGIN (?:CERTIFICATE|PRIVATE KEY|RSA PRIVATE KEY)-----.*-----END (?:CERTIFICATE|PRIVATE KEY|RSA PRIVATE KEY)-----\s*$/s';

        return (bool) preg_match($pemPattern, trim($content));
    }
}
