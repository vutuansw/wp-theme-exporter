<?php
/**
 * Zip Class
 *
 * @class     Zip
 * @package   WPThemeExporter
 * @category  Class
 * @author    WeDesignWeBuild
 * @license   GPLv3
 * @version   1.0
 */
namespace WPThemeExporter;

class Zip {

	private $settings;
	private $ignoreFileExt;
	private $ignoreDir;

	public function __construct( $data = array() ) {

		$defaults = array(
			'plugin_ignore_extensions' => '',
			'plugin_ignore_folder' => '',
			'theme_ignore_extensions' => '',
			'theme_ignore_folder' => 'document',
			'theme_plugin_folder' => 'plugins',
			'theme_plugin_folder_clear' => false
		);

		if ( !empty( $data ) ) {
			foreach ( $defaults as $key => $value ) {
				if ( isset( $data[$key] ) ) {
					$this->settings[$key] = sanitize_text_field( $data[$key]);
				}
			}
		}
	}

	public function setType( $type ) {

		if ( $type == 'theme' ) {
			$this->ignoreFileExt = explode( ',', $this->settings['theme_ignore_extensions'] );
			$this->ignoreDir = explode( ',', $this->settings['theme_ignore_folder'] );
		} else {
			$this->ignoreFileExt = explode( ',', $this->settings['plugin_ignore_extensions'] );
			$this->ignoreDir = explode( ',', $this->settings['plugin_ignore_folder'] );
		}

		$this->ignoreFileExt = array_map( function($value) {
			return trim( $value );
		}, $this->ignoreFileExt );

		$this->ignoreDir = array_map( function($value) {
			return trim( $value );
		}, $this->ignoreDir );
	}

	/**
	 * Zip a folder (include itself). 
	 * Usage: Zip::zipDir('/path/to/sourceDir', '/path/to/out.zip'); 
	 * 
	 * @param string $sourcePath Path of directory to be zip. 
	 * @param string $outZipPath Path of output zip file. 
	 */
	public function zipDir( $sourcePath, $outZipPath ) {
		
		if ( is_file( $outZipPath ) ) {
			unlink( $outZipPath );
		}

		$pathInfo = pathInfo( $sourcePath );
		$parentPath = $pathInfo['dirname'];
		$dirName = $pathInfo['basename'];

		$z = new \ZipArchive();
		$z->open( $outZipPath, \ZIPARCHIVE::CREATE );
		$z->addEmptyDir( $dirName );
		$this->folderToZip( $sourcePath, $z, strlen( "$parentPath/" ) );
		$z->close();
	}

	/**
	 * Add files and sub-directories in a folder to zip file. 
	 * @param string $folder 
	 * @param ZipArchive $zipFile 
	 * @param int $exclusiveLength Number of text to be exclusived from the file path. 
	 */
	private function folderToZip( $folder, &$zipFile, $exclusiveLength ) {
		$handle = opendir( $folder );
		while ( false !== $f = readdir( $handle ) ) {
			if ( $f != '.' && $f != '..' ) {
				$filePath = "$folder/$f";
				// Remove prefix from file path before add to zip. 
				$localPath = substr( $filePath, $exclusiveLength );
				if ( $this->isValidFile( $filePath ) && $this->isValidDir( $localPath ) ) {
					if ( is_file( $filePath ) ) {
						$zipFile->addFile( $filePath, $localPath );
					} elseif ( is_dir( $filePath ) ) {
						// Add sub-directory. 
						$zipFile->addEmptyDir( $localPath );
						$this->folderToZip( $filePath, $zipFile, $exclusiveLength );
					}
				}
			}
		}
		closedir( $handle );
	}

	public function isValidFile( $file ) {

		if ( strpos( $file, '.', strlen( $file ) - 1 ) ) {
			return false;
		}

		if ( $file == '.' || $file == '..' ) {
			return false;
		}

		if ( !empty( $this->ignoreFileExt ) && is_array( $this->ignoreFileExt ) ) {

			foreach ( $this->ignoreFileExt as $ext ) {

				if ( strpos( $file, $ext ) ) {
					return false;
				}
			}
		}

		return true;
	}

	public function isValidDir( $dir ) {

		$dir = explode( '/', $dir );
		$dir = isset( $dir[1] ) ? $dir[1] : false;

		if ( !empty( $this->ignoreDir ) && $dir ) {
			if ( in_array( $dir, $this->ignoreDir ) ) {
				return false;
			}
		}

		return true;
	}

	public function dump( $file ) {

		header( 'Content-Type: application/octet-stream' );
		header( "Content-Transfer-Encoding: Binary" );
		header( "Content-disposition: attachment; filename=\"" . basename( $file ) . "\"" );
		readfile( $file );
	}

}
