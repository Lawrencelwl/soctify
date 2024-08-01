<?php

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class S3Controller
{
    public function guidv4($data = null)
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function upload_file($mediaType, $media)
    {
        $bucketName = '';
        $IAM_KEY = '';
        $IAM_SECRET = '';
        $uuid = $this->guidv4();
        // Connect to AWS
        try {
            $s3 = S3Client::factory(
                array(
                    'credentials' => array(
                        'key' => $IAM_KEY,
                        'secret' => $IAM_SECRET
                    ),
                    'version' => 'latest',
                    'region' => 'ap-northeast-1'
                )
            );
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
        $keyName = 'post_media/' . $uuid . '.' . $mediaType;
        // $pathInS3 = 'https://s3.us-east-2.amazonaws.com/' . $bucketName . '/' . $keyName;

        // Add it to S3
        try {
            // Uploaded:
            $file = $media;
            $s3->putObject(
                array(
                    'Bucket' => $bucketName,
                    'Key' => $keyName,
                    'SourceFile' => $file,
                    'StorageClass' => 'REDUCED_REDUNDANCY'
                )
            );
        } catch (S3Exception $e) {
            die('Error:' . $e->getMessage());
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
        $mName = $uuid . '.' . $mediaType;
        $signedUrl = $this->get_file($uuid . '.' . $mediaType);
        $result = [
            "signedUrl" => "$signedUrl",
            "mediaName" => $mName
        ];
        return $result;
    }

    public function upload_public_file($mediaType, $media)
    {
        $bucketName = '';
        $IAM_KEY = '';
        $IAM_SECRET = '';
        $uuid = $this->guidv4();
        // Connect to AWS
        try {
            $s3 = S3Client::factory(
                array(
                    'credentials' => array(
                        'key' => $IAM_KEY,
                        'secret' => $IAM_SECRET
                    ),
                    'version' => 'latest',
                    'region' => 'ap-northeast-1'
                )
            );
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
        $keyName = 'post_media/' . $uuid . '.' . $mediaType;
        // $pathInS3 = 'https://s3.us-east-2.amazonaws.com/' . $bucketName . '/' . $keyName;

        // Add it to S3
        try {
            // Uploaded:
            $file = $media;
            $result = $s3->putObject(
                array(
                    'Bucket' => $bucketName,
                    'Key' => $keyName,
                    'SourceFile' => $file,
                    'ACL' => 'public-read',
                    'StorageClass' => 'REDUCED_REDUNDANCY'
                )
            );
            $url = $result['ObjectURL'];
        } catch (S3Exception $e) {
            die('Error:' . $e->getMessage());
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
        return $url;
    }


    public function get_file($mediaName)
    {
        $bucketName = 'soctify-bucket';
        $IAM_KEY = 'AKIA57QGC5BAZZ5AYO5Y';
        $IAM_SECRET = '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';

        // Connect to AWS
        try {
            $s3 = S3Client::factory(
                array(
                    'credentials' => array(
                        'key' => $IAM_KEY,
                        'secret' => $IAM_SECRET
                    ),
                    'version' => 'latest',
                    'region' => 'ap-northeast-1'
                )
            );
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
        $keyName = 'post_media/' . $mediaName;
        try {
            $expires = '+10 minutes';
            $result = $s3->getCommand('GetObject', [
                'Bucket' => $bucketName,
                'Key' => $keyName
            ]);
            $signedUrl = $s3->createPresignedRequest($result, $expires)->getUri();
            return $signedUrl;
            //Example of how to use signedUrl:
            //if videoFile:
            //"<video width='400' height='400' controls>
            //<source src=\"$signedUrl\" type='video/mp4'>
            //</video>"
            //else
            //"<img src=\"$signedUrl\">"
        } catch (Exception $exception) {
            exit("Please fix error with file downloading before continuing.");
        }
    }

    public function remove_file($mediaName)
    {
        $bucketName = 'soctify-bucket';
        $IAM_KEY = 'AKIA57QGC5BAZZ5AYO5Y';
        $IAM_SECRET = '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';

        // Connect to AWS
        try {
            $s3 = S3Client::factory(
                array(
                    'credentials' => array(
                        'key' => $IAM_KEY,
                        'secret' => $IAM_SECRET
                    ),
                    'version' => 'latest',
                    'region' => 'ap-northeast-1'
                )
            );
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
        $keyName = 'post_media/' . $mediaName;
        try {
            $result = $s3->deleteObject(
                array(
                    'Bucket' => $bucketName,
                    'Key' => $keyName
                )
            );
            return "success";
        } catch (Exception $exception) {
            exit("Please fix error with file downloading before continuing.");
        }
    }
}
