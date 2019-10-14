<?php

/**
 * Interface ResourcesInterface 资源接口
 */
interface ResourcesInterface {
    /**
     * 保存资源信息
     * @param $info 待保存资源信息
     * @return mixed
     */
    function resourceInfoSave($info);

    /**
     * 保存资源标引
     * @param $resource_id 资源编号
     * @return mixed
     */
    function resourceIndexSave($resource_id);

    /**
     * 保存资源文件信息
     * @return mixed
     */
    function resourceFileInfoSave();
}
/**
 * Class Resource 资源实体类
 */
abstract class Resources implements ResourcesInterface {

    private $resourceService;

    public function __construct()
    {
        // TODO: construct method.
        $this->resourceService = new ResourceService();
    }

    public function resourceInfoSave($info)
    {
        // TODO: Implement resourceInfoSave() method.
        return $this->resourceService->resourceSave($info);
    }

    public function resourceIndexSave($resource_id)
    {
        // TODO: Implement resourceIndexSave() method.
        return $this->resourceService->resourceSaveIndex($resource_id);
    }
}

/**
 * Class ImageResources
 */
class ImageResources extends Resources {

    public function resourceFileInfoSave()
    {
        // TODO: Implement resourceFileInfoSave() method.
    }
}

/**
 * Class VideoResources
 */
class VideoResources extends Resources {

    public function resourceFileInfoSave()
    {
        // TODO: Implement resourceFileInfoSave() method.
    }
}

/**
 * Class VideoResources
 */
class AudioResources extends Resources {

    public function resourceFileInfoSave()
    {
        // TODO: Implement resourceFileInfoSave() method.
    }
}

class ResourceService {

    public function resourceSave($info) {
        return 1;
    }

    public function resourceSaveIndex($resource_id) {
        return $resource_id;
    }
}

class MainTest {

    private $resource_type = ['image','video','audio'];

    private $resourceInterface;

    private $resourceModel;

    public function __construct()
    {
        $this->resourceInterface = ResourcesInterface::class;
        $this->resourceModel = array(
            'image' => new ImageResources(),
            'video' => new VideoResources(),
            'audio' => new AudioResources(),
        );
    }

    public function run() {
        $info = [];
        foreach ($this->resource_type as $value) {
            $info['resource_type'] = $value;
            $this->resourceInterface = $this->resourceModel[$value];
            $resource_id = $this->resourceInterface->resourceInfoSave($info);
            $this->resourceInterface->resourceIndexSave($resource_id);
        }
        $this->resourceInterface->resourceFileInfoSave();
    }
}

$mainTest = new MainTest();
$mainTest->run();


