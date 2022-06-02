<?php

namespace ReadCsv\Cron\Controller\Test;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\FileSystemException;

/**
 * Class Cart
 * @package Mageplaza\AbandonedCart\Controller\Checkout
 */
class Test extends Action
{

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csv;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $_dir;

    protected $productRepository;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;  

    /**
     * constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\File\Csv $csv,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Module\Dir\Reader $directoryList,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->file = $file;
        $this->csv = $csv;
        $this->logger = $logger;
        $this->directoryList = $directoryList;
        $this->_dir = $dir;
        $this->stockRegistry = $stockRegistry;

        parent::__construct($context);
    }

    /**
     * Recovery cart by cart link
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {   
        // die('2');
        //ftp-server lấy file mới về
        $connection_id = ftp_connect("sftp://133.242.20.60");
        $ftp_username = "s55103_web";
        $ftp_password = "4#KoDVvbmnwDB.k"."$"."Arhi";

        $file_path_my_pc = "var/import_history/";
        $file_path_ftp_server = "/uploads/skstock/skstock.csv";

        $login = ftp_login($connection_id, $ftp_username, $ftp_password);

        if (!$login) {
            echo "Connection to ftp server has failed!! ";
            exit;
        } else {
            echo "Connected has be done!!"."<br>";
        }
        // die();
        ftp_pasv($connection_id, true);
        if (ftp_get($connection_id, $file_path_my_pc.'skstock.csv', $file_path_ftp_server, FTP_ASCII)) {

            // //update sản phẩm
            try {
                // var_dump("1");
                // die();
                $data = file_get_contents($this->_dir->getRoot().'/var/import_history/skstock1.csv');
                $chunk_size = 200;
                $lines = explode(PHP_EOL, $data);
                $array = array();
                foreach ($lines as $line) {
                    $line=str_replace('”','',$line);
                    $array[] = str_getcsv($line);
                }
                $chunked_data = array_chunk($array, $chunk_size);
                foreach ($chunked_data  as $chunk) {
                    foreach($chunk as $item ){
                        try {
                            if ($item[0]) {
                                $sku = $item[0];
                                echo $item[3];
                                die();
                                // $product = $this->productRepository->get($item[0]);
                                $stockItem = $this->stockRegistry->getStockItemBySku($sku);
                                $stockItem->setQty((int)$item[3]);
                                $stockItem->setIsInStock((bool)$item[3]); // this line
                                $this->stockRegistry->updateStockItemBySku($sku, $stockItem);
                                // echo $productModel->getId();
                            } else {
                                continue;
                            } 
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                            continue;
                        }
                    }
                }
               echo "memory peak usage (Kb): " . memory_get_peak_usage()/1024;
            } catch (FileSystemException $e) {
                 $writer = new \Zend\Log\Writer\Stream(BP .'/var/log/tuyen.log');
                $logger =New \Zend\Log\Logger();
                $logger->addWriter($writer);
                $logger->info($e->getMessage()); 
                die($e->getMessage());
            }
            //trả về
            echo "---- File has been downloaded!!";
            ftp_close($connection_id);
            return true;
        } 
        else 
        {
            echo "---- fail ... ";
            echo "---- Connected has be stopped!!";
            return false;
        }
          ftp_close($connection_id);
    }
}
