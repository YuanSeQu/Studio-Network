<?php
/**
 * ����վCMS
 * ============================================================================
 * ��Ȩ���� 2015-2030 ɽ��������Ϣ�Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.rrzcms.com
 * ----------------------------------------------------------------------------
 * �����ҵ��;��ص��ٷ�����������Ȩ, �������𲻱�Ҫ�ķ��ɾ���.
 * ============================================================================
 */

namespace app\facade;

use think\Facade;

/**
 * http ����
 * @see \app\http
 * @package app\facade
 * @mixin \app\http
 * @method static mixed send($url = '', $options = []) ����http����
 */
class http extends Facade
{
    /**
     * ��ȡ��ǰFacade��Ӧ�����������Ѿ��󶨵����������ʶ��
     * @access protected
     * @return string
     */
    protected static function getFacadeClass() {
        return 'app\http';
    }
}