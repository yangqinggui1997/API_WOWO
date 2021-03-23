<?php

namespace App\Mail;

use App\Models\Infor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewUser extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // $thongtin = Infor::get_seo();
        // $lang = 'vn';
        // $footer = "<p><b>" . $thongtin['tenbaiviet_' . $lang] . "</b></p>";
        // $footer .= $thongtin['sodienthoai_vi'] != "" ? "<p>" . $glo_lang['so_dien_thoai'] . ": " . $thongtin['sodienthoai_vi'] . "</p>" : "";
        // $footer .= $thongtin['email_vi'] != "" ? "<p>" . $glo_lang['email'] . ": " . $thongtin['email_vi'] . "</p>" : "";
        // $footer .= $thongtin['diachi_' . $lang] != "" ? "<p>" . $glo_lang['dia_chi'] . ": " . $thongtin['diachi_' . $lang] . "</p>" : "";
        return $this->view('emails.new_user')->with([
            'header' => '',
            'content' => '',
            'footer' => ''
        ]);
    }
}
