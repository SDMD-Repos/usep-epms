<?php

namespace App\Http\Classes;

use JavaClass;
use Java;

header("Content-Type: text/html; charset=utf-8");
class Jasperreport
{
    public function checkJavaExtension()
    {
        if (!extension_loaded('java')) {
            $sapi_type = php_sapi_name();
            $port = (isset($_SERVER['SERVER_PORT']) && (($_SERVER['SERVER_PORT']) > 1024)) ? $_SERVER['SERVER_PORT'] : '8090';
            if ($sapi_type == "cgi" || $sapi_type == "cgi-fcgi" || $sapi_type == "cli") {
                require_once(config('tomcat.tomcat_java'));
                return true;
            } else {
                if (!(@require_once(config('tomcat.tomcat_java')))) {
                    require_once(config('tomcat.tomcat_java'));
                }
            }
        }
        if (!function_exists("java_get_server_name")) {
            return "The loaded java extension is not the PHP/Java Bridge";
        }
        return true;
    }


    public function showReport($template_name, $parameters, $tableData = array(), $repFormat = 'pdf', $fileName = '')
    {

        $x = $this->checkJavaExtension();
        $report = $template_name;
        $compileManager = new JavaClass("net.sf.jasperreports.engine.JasperCompileManager");
        $compileManager->__client->cancelProxyCreationTag = 0;
        $report = $compileManager->compileReport(realpath(config('tomcat.tomcat_resource') . $report . '.jrxml'));
        java_set_file_encoding("UTF-8");
        $fillManager = new JavaClass("net.sf.jasperreports.engine.JasperFillManager");
        $params = new Java("java.util.HashMap");
        $start = microtime(true);

        #------------- DATA -------------------------------------------------------------------------------------
        #------------- DATA -------------------------------------------------------------------------------------

        foreach ($parameters as $key => $value) {
            if(is_array($value)){
                $arrayList = new java( 'java.util.ArrayList' );

                foreach( $value as $v ) {
                    $arrayList->add($v);
                }

                $newSubset = new Java("net.sf.jasperreports.engine.data.JRBeanCollectionDataSource", $arrayList);

                $params->put($key, $newSubset);
            }else{
                $params->put($key, $value);
            }
        }

        $data = $tableData;

        #------------- DATA -------------------------------------------------------------------------------------
        #------------- DATA -------------------------------------------------------------------------------------
        $jCollection = new Java("java.util.ArrayList");

        foreach ($data as $i => $row) {
            $jMap = new Java('java.util.HashMap');
            foreach ( $row as $field => $value ) {
                if(gettype($value) === 'array') {
                    $jMap->put($field, new Java("java.util.ArrayList"));
                    foreach($value as $val) {
                        $subMap = new Java("java.util.HashMap");
                        foreach($val as $f => $v ) {
                            $subMap->put($f, $v);
                        }
                        $jMap->get($field)->add($subMap);
                    }
                } else {
                    $jMap->put($field, $value);
                }

            }
            $jCollection->add($jMap);
        }

        $jMapCollectionDataSource = new Java("net.sf.jasperreports.engine.data.JRMapCollectionDataSource", $jCollection);
        $jasperPrint = $fillManager->fillReport($report, $params, $jMapCollectionDataSource);
        $end = microtime(true);
        /*$outputPath  = tempnam(env('TOMCAT_TMP'), '');
        chmod($outputPath, 0777);*/

        if (strtoupper($repFormat) == 'PDF') {
            $fileName = preg_replace('/\s+/', '_', $fileName);
            $outputPath  = config('tomcat.file_backend_storage') . $fileName . ".pdf";

            /*header("Content-Type: application/pdf; charset=utf-8");
            header("Content-Disposition: filename=".$fileName);*/
            $exportManager = new JavaClass("net.sf.jasperreports.engine.JasperExportManager");
            $exportManager->exportReportToPdfFile($jasperPrint, $outputPath);
            chmod($outputPath, 0777);
        } else {
            header("Content-type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=output.xls");
            $exportManager = new java("net.sf.jasperreports.engine.export.JRXlsExporter");
            $exportManager->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->JASPER_PRINT, $jasperPrint);
            $exportManager->setParameter(java("net.sf.jasperreports.engine.JRExporterParameter")->OUTPUT_FILE_NAME, $outputPath);
            $exportManager->exportReport();
        }

        /*readfile($outputPath);
        unlink($outputPath);*/
    }
}
