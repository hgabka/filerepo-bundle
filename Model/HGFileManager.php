<?php

namespace HG\FileRepositoryBundle\Model;

use HG\FileRepositoryBundle\Entity\HGFile as hgabkaFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use HG\FileRepositoryBundle\Model\FileRepositoryFileManagerInterface;
/**
 * File repository
 *
 */
class HGFileManager implements FileRepositoryFileManagerInterface
{
 /**
   * Tipusok
   *
   * @var array
   */
  protected $types;

  /**
   * A fileRepository alap konyvtara
   *
   * @var string
   */
  protected $baseDir;

  /**
   * File kiterjesztesek es a hozzajuk tartozo mime type-ok
   *
   * @var array
   */
  protected $extensionMimeTypeMapping = array(
    ''        => 'application/octet-stream',
    'swf'     => 'application/x-shockwave-flash',
    'pdf'     => 'application/pdf',
    'avi'     => 'video/x-msvideo',
    'bz'      => 'application/x-bzip',
    'bz2'     => 'application/x-bzip2',
    'css'     => 'text/css',
    '3gp'     => 'video/3gpp',
    '3g2'     => 'video/3gpp2',
    '7z'      => 'application/x-7z-compressed',
    'bin'     => 'application/octet-stream',
    'bmp'     => 'image/bmp',
    'torrent' => 'application/x-bittorent',
    'sh'      => 'application/x-sh',
    'c'       => 'text/x-c',
    'csv'     => 'text/csv',
    'deb'     => 'application/x-debian-package',
    'gif'     => 'image/gif',
    'jar'     => 'application/java-archive',
    'class'   => 'application/java-vm',
    'jnlp'    => 'application/x-java-jnlp-file',
    'java'    => 'text/x-java-source',
    'js'      => 'application/javascript',
    'json'    => 'application/json',
    'jpg'     => 'image/jpeg',
    'jpeg'    => 'image/jpeg',
    'latex'   => 'application/x-latex',
    'm3u'     => 'application/x-mpegurl',
    'exe'     => 'application/x-msdownload',
    'xls'     => 'application/vnd.ms-excel',
    'pptx'    => 'application/vnd.openxmlforamts-officedocument.presentationml.presentation',
    'ppsx'    => 'application/vnd.openxmlforamts-officedocument.presentationml.slideshow',
    'xlsx'    => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'docx'    => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'ppt'     => 'application/vnd.ms-powerpoint',
    'wmv'     => 'video/x-ms-wmv',
    'doc'     => 'application/vnd.ms-word',
    'mpeg'    => 'video/mpeg',
    'mp4'     => 'video/mp4',
    'odf'     => 'application/vnd.oasis.opendocument.formula',
    'rar'     => 'application/x-rar-compressed',
    'rtf'     => 'application/rtf',
    'rss'     => 'application/rss+xml',
    'xml'     => 'application/xml',
    'svg'     => 'images/svg+xml',
    'txt'     => 'text/plain',
    'wsdl'    => 'application/wsdl+xml',
    'xhtml'   => 'application/xhtml+xml',
    'zip'     => 'application/zip',
    'png'     => 'image/png',
  );

  protected $entityManager;
  protected $kernel;
  protected $secureDirname;
  protected $securityContext;
  protected $secureRole = null;
  protected $uploadManager;

  /**
   * Konstruktor
   *
   */
  public function __construct($entityManager, $uploadManager, $types, $kernel, $securityContext)
  {
    $this->entityManager = $entityManager;
    $this->types = $types;
    $this->kernel = $kernel;
    $this->securityContext = $securityContext;
    $this->uploadManager = $uploadManager;
    
    $this->uploadManager->registerRequestType('file_repository', $this);
  }
  
  public function registerType($name, $is_secure = false, $filename = '%id%', $secure_role = null)
  {
    if (!isset($this->types[$name]))
    {
      $this->types[$name] = array(
      'is_secure' => $is_secure,
      'filename' => $filename,
      'secure_role' => $secure_role
      );
    }  
  }

  public function setSecureRole($role)
  {
    $this->secureRole = $role;
  }

  public function getSecureRole()
  {
    return $this->secureRole;
  }


  public function getKernel()
  {
    return $this->kernel;
  }

  public function getWebDir()
  {
    if (method_exists($this->kernel, 'getWebDir'))
    {
      return $this->kernel->getWebDir();
    }

    return realpath($this->kernel->getRootDir(). DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .'web');
  }

  public function setSecureDirname($secureDirname)
  {
    $this->secureDirname = $secureDirname;
  }

  public function getSecureDirname()
  {
    return $this->secureDirname;
  }

  public function getSecureDir()
  {
    return realpath($this->kernel->getRootDir(). DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $this->secureDirname);
  }

  public function setBaseDir($baseDir)
  {
    $this->baseDir = $baseDir;
  }

  /**
   * Letolto link megjelenitese megjelenites
   *
   * @param int $id
   * @param string $type
   * @return str
   */
  public function show($id)
  {
    $file = $this->getValidatedFileEntity($id);

    if ($this->isFileSecure($file))
    {
      throw new LogicException('Nem publikus kepekhez nem lehet link-et generalni. Hasznald a download metodust!');
    }

    return $this->getFilePath($file, false);
  }

  /**
   * Letolt egy file-t
   *
   * @param int $id
   * @param string $filename
   */
  public function download($id, $filename = null)
  {
    $file = $this->getValidatedFileEntity($id);

    if ($this->isFileSecure($file))
    {
      $fileRole = $this->getFileSecureRole($file);
      if (!empty($fileRole) && !$this->securityContext->isGranted($fileRole))
      {
        throw new AccessDeniedException();
      }
    }

    $path = $this->getFilePath($file);

    if (!file_exists($path) || !is_readable($path))
    {
      throw new LogicException('A rekordhoz tartozo file nem talalhato, vagy nem olvashato: ' . $path);
    }

    $response = new Response();
    $response->headers->set('Content-Type', $file->getFilMimeType());
    $response->headers->set('Pragma', 'public');
    $response->headers->set('Expires', '0');
    $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
    $response->headers->set('Content-Transfer-Encoding', 'binary');
    $response->headers->set('Content-Length', $file->getFilSize());
    $response->headers->set('Content-Disposition',
      sprintf('attachment; filename="%s"', empty($filename) ? $file->getFilOriginalFilename() : $filename)
    );

    $response->setContent(file_get_contents($path));

    return $response;
  }

  public function getFromUploadedFile(UploadedFile $resource, $type, $subDir = null, $withFlush = false)
  {
    $file = $this->createFile($type);
    $this->createFromUploadedFile($file, $resource, $subDir, $withFlush);

    return $file;
  }

  /**
   * Az adott UploadedFile objektumot elmenti
   *
   * @param UploadedFile $resource
   * @param string $type
   *
   * @return int
   */
  public function saveFromUploadedFile(UploadedFile $resource, $type, $subDir = null)
  {
    return $this->getFromUploadedFile($resource, $type, $subDir, true)->getFilId();
  }

  /**
   * Az adott elérési út alatti fájl elmenti
   *
   * @param string $path
   * @param string $type
   *
   * @return int
   */
  public function saveFromPath($path, $type, $subDir = null)
  {
    $file = $this->createFile($type);
    $this->createFromPath($file, $path, $subDir);

    return $file->getFilId();
  }

  /**
   * Elmenti a fájlt az adott tartalommal
   *
   * @param string $content
   * @param string $type
   * @param string|null $filename
   *
   * @return int
   */
  public function saveFromContent($content, $type, $filename, $mimeType, $subDir = null)
  {
    $file = $this->createFile($type);
    $this->createFromContent($file, $content, $filename, $mimeType, $subDir);

    return $file->getFilId();
  }

  public function remove($file, $withFlush = false)
  {
    if (!$file)
    {
      return;
    }

    if (file_exists($this->getFilePath($file)))
    {
      unlink($this->getFilePath($file));
    }

    $em = $this->getEntityManager();

    $em->remove($file);
    if ($withFlush)
    {
      $em->flush();
    }
  }

  /**
   * Torol egy file-t
   *
   * @param int $id
   */
  public function delete($id, $withFlush = true)
  {
    if (empty($id))
    {
      return;
    }
    
    $file = $this->getEntityRepository()->findOneBy(array('fil_id' => $id));
    
    $this->remove($file, $withFlush);
  }

  /**
   * Visszaadja a file eleresi utjat
   *
   * @param hgabkaFile $file
   * @param bool $fullpath
   * @return string
   */
  public function getFilePath(hgabkaFile $file, $fullpath = true)
  {
    $fileName = $file->getFilFilename();

    return $this->getFileDir($file, $fullpath) .
      DIRECTORY_SEPARATOR . (empty($fileName) ? $file->getFilOriginalFilename() : $fileName);
  }

  /**
   * Visszaadja a file eleresi utjat
   *
   * @param int $id
   * @param bool $fullpath
   * @return string
   */
  public function getFilePathById($id, $fullpath = true)
  {
    $file = $this->getValidatedFileEntity($id);

    return $file ? $this->getFilePath($file, $fullpath) : '';
  }

  public function getFileDir(hgabkaFile $file, $fullpath = true)
  {
    $subDir = $file->getFilSubdirectory();

    $idDir = (empty($subDir) ? intval(floor($file->getFilId() / 1000)) : $subDir);

    return $this->getTypeDir($file->getFilType(), $fullpath) .
      DIRECTORY_SEPARATOR . $idDir;
  }

  /**
   * Visszaadja a base_dir-t
   *
   * @return string
   */
  public function getBaseDir()
  {
    return $this->baseDir;
  }

  /**
   * Elment egy file-t egy UploadedFile peldany alapjan
   *
   * @param hgabkaFile $file
   * @param UploadedFile $validatedFile
   * @return hgabkaFile
   */
  protected function createFromUploadedFile(hgabkaFile $file, UploadedFile $validatedFile, $subDir = null, $withFlush = true)
  {
    $this->configureFile(
      $file,
      $validatedFile->getClientOriginalName(),
      $this->guessMimeType($validatedFile),
      $subDir,
      $validatedFile->getClientSize(),
      $withFlush
    );
    $fileName = $file->getFilFilename();

    $validatedFile->move($this->getFileDir($file), (empty($fileName) ? $file->getFilOriginalFilename() : $fileName));
  }

  /**
   * Elment egy file-t az eleresi utja alapjan
   *
   * @param hgabkaFile $file
   * @param string $path
   */
  protected function createFromPath(hgabkaFile $file, $path, $subDir = null, $withFlush = true)
  {
    $extension = $this->getExtensionFromPath($path);

    $this->configureFile(
      $file,
      substr($path, strrpos($path, '/') + 1),
      $this->guessMimeType($path),
      $subDir,
      filesize($path),
      $withFlush
    );

    copy($path, $this->getFilePath($file));
  }

  /**
   * Elmenti a megadott tartalmat
   *
   * @param hgabkaFile $file
   * @param string $content
   * @param string $filename
   * @param string $mimeType
   */
  protected function createFromContent(hgabkaFile $file, $content, $filename, $mimeType, $subDir = null, $withFlush = true)
  {
    $this->configureFile(
      $file,
      $filename,
      $mimeType,
      $subDir,
      mb_strlen($content),
      $withFlush
    );

    file_put_contents($this->getFilePath($file), $content);
  }

  /**
   * Beallitja a file-on a szukseges parametereket
   *
   * @param hgabkaFile $file
   * @param string $originalFileName
   * @param string $mimeType
   * @param int $size
   */
  protected function configureFile(hgabkaFile $file, $originalFileName, $mimeType, $subDir, $size, $withFlush = true)
  {
    $file->setFilOriginalFilename($originalFileName);
    $em = $this->getEntityManager();

    $em->persist($file);
    if ($withFlush)
    {
      $em->flush($file);
    }

    $file->setFilFilename($this->transformFilename($file));
    $file->setFilSubdirectory($subDir);
    $fileDir = $this->getFileDir($file);

    if (!is_dir($fileDir))
    {
      if (!mkdir($fileDir, 0777, true))
      {
        throw new IOException('Nem sikerült létrehozni a könyvtárat: '. $fileDir);
      }

      chmod($fileDir, 0777);
    }

    $this->findFirstFreename($file);

    $file->setFilMimeType($mimeType);
    $file->setFilSize($size);

    $em->persist($file);

    if ($withFlush)
    {
      $em->flush($file);
    }

  }

  /**
   * Ha nem megfelelo a kiterjesztes akkor kivetelt dob
   *
   * @param string $extension
   * @return string
   */
  protected function getMimeTypeByExtension($extension)
  {
    if (!isset($this->extensionMimeTypeMapping[$extension]))
    {
      return false;
    }

    return $this->extensionMimeTypeMapping[$extension];
  }

  /**
   * Visszaad egy validalt file rekord-ot
   *
   * @param int $id
   * @return hgabkaFile
   */
  protected function getValidatedFileEntity($id)
  {
    if (null === $id)
    {
      throw new InvalidArgumentException('Az azonosito nem lehet null');
    }

    $file = $this->getEntityRepository()->findOneBy(array('fil_id' => $id));

    if (!$file || null === $file->getFilId())
    {
      throw new InvalidArgumentException('Nincs ilyen azonositoju file: ' . $id);
    }

    return $file;
  }

  /**
   * Megmondja, hogy az adott kep tipusa alapjan publikus-e
   *
   * @param hgabkaFile $file
   * @return bool
   */
  public function isFileSecure(hgabkaFile $file)
  {
    return $this->types[$file->getFilType()]['is_secure'];
  }

  /**
   * Megmondja, hogy az adott kep tipusa alapjan publikus-e
   *
   * @param hgabkaFile $file
   * @return bool
   */
  public function getFileSecureRole(hgabkaFile $file)
  {
    if (!$this->types[$file->getFilType()]['is_secure'])
    {
      return null;
    }

    if (!isset($this->types[$file->getFilType()]['secure_role']))
    {
      return $this->secureRole;
    }

    return $this->types[$file->getFilType()]['secure_role'];
  }

  /**
   * Validalja, majd ha rendben van visszaadja a tipust
   *
   * @param string $type
   * @return string
   */
  protected function validateType($type)
  {
    if (!isset($this->types[$type]))
    {
      throw new InvalidArgumentException('Nincs ilyen tipus: ' . $type);
    }

    return $type;
  }

  /**
   * Létrehoz egy új hgabkaFile objektumot az adott típussal
   *
   * @param string $type
   * @return hgabkaFile
   */
  protected function createFile($type)
  {
    $file = new hgabkaFile();

    $this->validateType($type);

    $file->setFilType($type);

    return $file;
  }

  /**
   * A fájlnévből kiszedi az extensiont
   *
   * @param string $path
   * @return string
   */
  public function getExtensionFromPath($path)
  {
    $lastDot = strrpos($path, '.');

    if ($lastDot === false)
    {
      return '';
    }

    return substr($path, $lastDot + 1);
  }

  /**
   * Megpróbálja kitalálni a mime type-ot
   *
   * @param UploadedFile|string $resource vagy az UploadedFile objektum, vagy az elérési út
   */
  protected function guessMimeType($resource)
  {
    if ($resource instanceof UploadedFile)
    {
      $path = $resource->getRealPath();
      $type = $resource->getMimeType();
      $extension = $resource->getClientOriginalExtension();
    }
    elseif (is_file($resource))
    {
      $path = $resource;
      $type = false;
      $extension = $this->getExtensionFromPath($path);
    }
    else
    {
      return '';
    }

    // ha finfo nem, akkor mi, extension alapján
    $mime = $this->getMimeTypeByExtension($extension);
    if ($mime !== false)
    {
      return $mime;
    }

    // Először finfo_file
    if (function_exists('finfo_file'))
    {
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime = finfo_file($finfo, $path);

      if ($mime !== false)
      {
        return $mime;
      }
    }


    // ha mi sem, és OploadedFile, akkor amit ő mond
    if ($type !== false)
    {
      return $type;
    }

    // ha most sincs meg, akkor exception
    throw new UnexpectedValueException('Nincs a kiterjeszteshez tartozo mime-type: ' . $extension);
  }

  /**
  * Ellenőrzi, hogy nem létezik-e a file nevével megegyező nevű fájl
  * Ha létezik, prefixel, amíg nem talál olyat, ami nincs
  *
  * @param hgabkaFile $file
  * @return string
  */
  public function findFirstFreeName(hgabkaFile $file)
  {
    $pi = pathinfo($this->getFilePath($file));

    $oFn = $pi['filename'];
    $oEx = $pi['extension'];

    $i = 1;
    while (file_exists($path = $this->getFilePath($file)))
    {
      $nFn = $oFn.'_'.($i++);
      $file->setFilFilename($nFn . '.'. $oEx);
    }

    return $this->getFilePath($file);
  }

  /**
   * Megmondja, hogy az adott fajltipus publikus-e
   *
   * @param string $type
   * @return bool
   */
  public function isTypeSecure($type)
  {
    $this->validateType($type);

    return isset($this->types[$type]['is_secure']) && $this->types[$type]['is_secure'];
  }

  /**
   * Visszaadja az adott típus könyvtárát
   *
   * @param string $type
   * @return bool
   */
  public function getTypeDir($type, $fullpath = true)
  {
    return ($fullpath ? ($this->isTypeSecure($type) ? $this->getSecureDir() : $this->getWebDir()) : '') .
      DIRECTORY_SEPARATOR . $this->getBaseDir() .
      DIRECTORY_SEPARATOR . $type;
  }

  /**
  * Átalakítja a fájlnevet az app.yml-ben definiált mintának megfelelően
  *
  * @param hgabkaFile $file
  *
  * @return string az átalakított fájlnév
  */
  protected function transformFilename(hgabkaFile $file)
  {
    $typeConfig = $this->types;
    $originalFileName = $file->getFilOriginalFilename();

    if (!isset($typeConfig[$file->getFilType()]['filename']))
    {
      return $originalFileName;
    }

    $newFileName = $typeConfig[$file->getFilType()]['filename'];
    // date konstansok keresése
    preg_match_all('/\%(.{1})\%/', $newFileName, $matches);

    $subs = array();
    if (isset($matches[0], $matches[1]))
    {
      foreach ($matches[0] as $key => $pattern)
      {
        $subs[$pattern] = date($matches[1][$key]);
      }
    }

    $subs['%rand%'] = rand();
    $subs['%uniqid%'] = uniqid();
    $subs['%id%'] = $file->getFilId() ? : $this->getNextId();
    $oExt = $this->getExtensionFromPath($originalFileName);

    return strtr($newFileName, $subs) . (empty($oExt) ? '' : '.'. $oExt);
  }


  public function getEntityManager()
  {
    return $this->entityManager;
  }

  public function getFileObject($id)
  {
    if (empty($id))
    {
      return null;
    }

    $file = $this->getEntityRepository()->findOneBy(array('fil_id' => $id));
    $id = $file ? $file->getFilId() : null;

    return !empty($id) ? $file : null;
  }

  public function getTypes()
  {
    return $this->types;
  }

  public function getNextId()
  {
    return $this->getEntityRepository()->getMaxId() + 1;
  }

  public function getEntityRepository()
  {
    return $this->getEntityManager()->getRepository('HGFileRepositoryBundle:HGFile');
  }
}
