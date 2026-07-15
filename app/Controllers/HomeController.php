<?php
namespace App\Controllers;

/**
 * 首页控制器
 */
class HomeController extends BaseController
{
    public function index()
    {
        // 检查首页跳转设置
        $settings = $this->db->fetch("SELECT * FROM {$this->db->getPrefix()}settings WHERE id = 1");
        $homeRedirect = $settings['homepage_redirect'] ?? '';
        
        if ($homeRedirect === 'login') {
            header('Location: /user/login');
            exit;
        }
        
        $platforms = $this->db->fetchAll(
            "SELECT name, platform, icon FROM {$this->db->getPrefix()}platforms WHERE status = 1 ORDER BY sort"
        );

        $this->view('home/index', [
            'platforms' => $platforms,
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 公开的API文档页面
     */
    public function docs()
    {
        $this->view('home/docs', [
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 公司介绍页面
     */
    public function about()
    {
        $this->view('home/about', [
            'csrf_token' => $this->generateCsrf(),
        ]);
    }

    /**
     * 联系方式页面
     */
    public function contact()
    {
        $this->view('home/contact', [
            'csrf_token' => $this->generateCsrf(),
        ]);
    }
}
