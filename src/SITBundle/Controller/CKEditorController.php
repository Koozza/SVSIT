<?php

namespace SITBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CKEditorController extends Controller
{

    /**
     * Upload POST action.
     * Returns a JSON response with filename, url and error.
     *
     * @param Request $request
     *
     * @return Response
     * @Route("/admin/ckeditor/upload")
     */
    public function uploadAction(Request $request)
    {
        define('SITE_ROOT', realpath(dirname(__FILE__)));


        $targetDir = "/uploads/CKEditor/";
        $targetFile = $targetDir . basename($_FILES["upload"]["name"]);
        $uploadSucces = 1;
        $imageFileType = pathinfo($targetFile, PATHINFO_EXTENSION);

        // Check if image file is a actual image or fake image
        if (isset($_POST["submit"])) {
            $check = getimagesize($_FILES["upload"]["tmp_name"]);
            if ($check !== false) {
                $uploadSucces = 1;
            } else {
                $error = "Bestand is geen afbeelding.";
                $uploadSucces = 0;
            }
        }

        // Check if file already exists
        if (file_exists(SITE_ROOT . '/../../../web' . $targetFile)) {
            $temp = explode(".", $_FILES["upload"]["name"]);
            $extension = array_pop($temp);
            $filename = implode('.', $temp);
            $targetFile = $targetDir . $filename . '_' . round(microtime(true)) . '.' . $extension;
        }

        // Check file size
        if ($_FILES["upload"]["size"] > 130000) {
            $error = "Bestsand is groter dan 13MB.";
            $uploadSucces = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif"
        ) {
            $error = "Sorry, alleen JPG, JPEG, PNG & GIF bestanden zijn toegestaan.";
            $uploadSucces = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadSucces == 1) {
            if (!move_uploaded_file($_FILES["upload"]["tmp_name"], SITE_ROOT . '/../../../web' . $targetFile)) {
                $uploadSucces = 0;
                $error = "Sorry er is iets misgegaan met het uploaden van het bestand.";
            }
        }

        if ($uploadSucces == 1) {
            $obj = (object)array(
                'uploaded' => $uploadSucces,
                'filename' => basename($_FILES["upload"]["name"]),
                'url'      => $targetFile
            );
        } else {
            $obj = (object)array(
                'uploaded' => $uploadSucces,
                'filename' => basename($_FILES["upload"]["name"]),
                'url'      => $targetFile,
                'error'    => (object)array('message' => $error)
            );
        }


        return new Response(json_encode($obj));
    }
}