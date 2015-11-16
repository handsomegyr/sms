<?php
namespace apikey\Yunpian;

/**
 * 用户管理
 *
 * @author guoyongrong <handsomegyr@gmail.com>
 */
class Client
{

    protected $apikey;

    private $_url = 'http://yunpian.com/v1/sms/';

    public function __construct($apikey, $options = array())
    {
        $this->apikey = $apikey;
    }

    /**
     * 1、智能匹配模版发送
     * URL：http://yunpian.com/v1/sms/send.json
     * 功能说明：该接口要求提前在云片后台添加模板，提交短信时，系统会自动匹配审核通过的模板，匹配成功任意一个模板即可发送。系统已提供的默认模板可以直接使用。
     * 特别说明：验证码短信，请在手机验证环节，加入图片验证码，以免被恶意攻击。了解详情
     * 访问方式：POST
     * 参数：
     * 参数名	类型	是否必须	描述	示例
     * apikey	String	是	用户唯一标识	9b11127a9701975c734b8aee81ee3526
     * mobile	String	是	接收的手机号;发送多个手机号请以逗号分隔，一次不要超过100条国际短信仅支持单号码发送，格式必须是"+"号开头，带有国际地区前缀号码的完整号码，否则将被认为是中国地区的号码 （针对国际短信，mobile参数会自动格式化到E.164格式，可能会造成传入mobile参数跟后续的状态报告中的号码不一致。E.164格式说明，参见： https://en.wikipedia.org/wiki/E.164）	单号码：15205201314 多号码：15205201314,15205201315国际短信：+93701234567
     * text	String	是	短信内容	【云片网】您的验证码是1234
     * extend	String	否	扩展号。默认不开放，如有需要请联系客服申请	001
     * uid	String	否	用户自定义唯一id。最大长度不超过256的字符串。默认不开放，如有需要请联系客服申请	10001
     * callback_url	String	否	本条短信状态报告推送地址
     * 默认不开放，如有需要请联系客服申请	http://your_receive_url_address
     * 部分返回参数说明：
     * 返回参数名	类型	描述
     * count	Integer	成功发送的短信个数
     * fee	Integer	扣费条数，70个字一条，超出70个字时按每67字一条计
     * fee(国际短信)	Double	扣费金额，单位：元，类型：双精度浮点型/double
     * sid	Long(64位)	短信id，多个号码时以该id+各手机号尾号后8位作为短信id。64位整型， 对应Java和C#的Long，不可用int解析
     * 调用成功的返回值示例：
     * {
     * "code": 0,
     * "msg": "OK",
     * "result": {
     * "count": 1, //成功发送的短信个数
     * "fee": 1, //扣费条数，70个字一条，超出70个字时按每67字一条计
     * "sid": 1097 //短信id；多个号码时以该id+各手机号尾号后8位作为短信id,
     * //（数据类型：64位整型，对应Java和C#的long，不可用int解析)
     * }
     * }
     * 国际短信调用成功的返回值示例：
     * {
     * "code": 0,
     * "msg": "OK",
     * "result": {
     * "count": 1, //成功发送的短信个数
     * "fee": 0.04, //扣费金额，单位：元，类型：双精度浮点型/double
     * "sid": 1097 //短信id；
     * }
     * }
     * 防骚扰过滤：默认开启。过滤规则：同1个手机发相同内容，30秒内最多发送1次，5分钟内最多发送3次。
     * 相关介绍：查看开发流程、查看代码示例
     */
    public function send($mobile, $text, $extend = "", $uid = "", $callback_url = "")
    {
        $params = array();
        $params['apikey'] = $this->apikey;
        $params['mobile'] = urlencode($mobile);
        if (! empty($extend)) {
            $params['extend'] = urlencode($extend);
        }
        if (! empty($uid)) {
            $params['uid'] = urlencode($uid);
        }
        if (! empty($callback_url)) {
            $params['callback_url'] = urlencode($callback_url);
        }
        
        $rst = $this->post($this->_url . 'send.json', $params);
        if (! empty($rst['code'])) {
            throw new \Exception($rst['msg'], $rst['code']);
        } else {
            return $rst;
        }
    }

    /**
     * 获取微信服务器信息
     *
     * @param string $url            
     * @param array $params            
     * @return mixed
     */
    public function get($url, $params = array())
    {
        $client = new \Guzzle\Http\Client($this->_url);
        $request = $client->get($url, array(), array(
            'query' => $params
        ));
        $request->getCurlOptions()->set(CURLOPT_SSLVERSION, 1); // CURL_SSLVERSION_TLSv1
        $response = $client->send($request);
        if ($response->isSuccessful()) {
            return $response->json();
        } else {
            throw new \Exception("云片短信服务器未有效的响应请求");
        }
    }

    /**
     * 推送消息给到微信服务器
     *
     * @param string $url            
     * @param array $params            
     * @return mixed
     */
    public function post($url, $params = array())
    {
        $client = new \Guzzle\Http\Client($this->_url);
        $client->setDefaultOption('body', $params);
        $request = $client->post($url);
        $request->getCurlOptions()->set(CURLOPT_SSLVERSION, 1); // CURL_SSLVERSION_TLSv1
        $response = $client->send($request);
        if ($response->isSuccessful()) {
            return $response->json();
        } else {
            throw new \Exception("云片短信服务器未有效的响应请求");
        }
    }
}
