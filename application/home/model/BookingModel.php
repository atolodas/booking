<?php
namespace app\home\model;
use think\Model;

class BookingModel extends Model
{
    // 关闭自动写入update_time字段
    protected $updateTime = false;

    protected $token_salt = 'bki';  //生成token的盐值

    public function getFSexAttr($value){
        $arr = ['未知','男','女'];
        if(isset($arr[$value])){
            return $arr[$value];
        }else{
            return $value;
        }
    }
    /**
     * 得到信息   单条
     * @param string $where 条件
     * @param string $join 连表
     * @param string $field 字段
     * @return mixed
     */
    public function getInfo($where = '', $join = '', $field = '*', $order = '')
    {
        if ($join) {
            //需要联表操作
            $res = $this->alias('a')->where($where)->join($join)->field($field)->order($order)->find();
        } else {
            //不需要联表操作
            $res = $this->alias('a')->where($where)->field($field)->order($order)->find();
        }
//        if($res)$res = $res->toArray();   //不能转换成数组，否则get...Attr会出错
        return $res;
    }
    /**
     * 得到信息   多条
     * @param array $where 条件
     * @param array $join 连表
     * @param string $field 字段R
     * @param string $order 排序 默认倒序
     * @param string $limit 条数
     * @return mixed
     *
     * 案例：
     * $join = [['mall__common','mall_._commonid = mall__common._commonid','left']];
     * $res_list = $model->getListInfo('','*','_id desc',$join,'0,10');
     **/
    public function getListInfo($where = [], $join = [], $field = '*', $order = '', $limit = '')
    {
        if(!$order)$order = $this->pk.' desc';
        $res_list = $this->alias('a')->where($where)->join($join)->field($field)->order($order)->limit($limit)->select()->toArray();
        return $res_list;
    }
    /**
     * 分页显示列表
     * @param array $where
     * @param array $join 连表
     * @param string $field
     * @param int $pagesize 每页数
     * @param string $order 排序
     * @return mixed
     */
    public function getListPageTotalInfo($where = [], $join = [], $field = '*', $pagesize = 10, $order = '')
    {
        if(!$order)$order = $this->pk.' desc';
        $res = $this->alias('a')->where($where)->join($join)->field($field)->order($order)->paginate($pagesize)->toArray();
        return $res;
    }
    /**
     * 删除数据
     * @param array $where
     * @param array $data
     * @return mixed
     */
    public function delInfo($where = [])
    {
        $res = $this->where($where)->delete();
        return $res;
    }


}