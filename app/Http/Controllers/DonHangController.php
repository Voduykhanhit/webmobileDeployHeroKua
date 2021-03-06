<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Requests;
use App\SanPham;
use App\ThuongHieu;
use App\DanhMucSanPham;
use App\Slide;
use App\HinhAnhSanPham;
use App\GiaoHang;
use App\KhachHang;
use App\ThanhToan;
use App\ChiTietHoaDon;
use App\HoaDon;
use App\MaGiamGia;
use Illuminate\Support\Facades\DB;
use Session;
use Cart;
use PDF;
session_start();

class DonHangController extends Controller
{
    public function getDanhSach(){
        $donhang = HoaDon::orderBy('created_at','DESC')->paginate(5);
        return view('admin.donhang.danhsach',compact('donhang'));
    }
    public function getChiTiet($id){
        $chitiethoadon = ChiTietHoaDon::where('order_code',$id)->get();
        $hoadon = HoaDon::where('order_code',$id)->first();
        
            $customer_id = $hoadon->customer_id;
            $shipping_id = $hoadon->shipping_id;
            $payment_id = $hoadon->payment_id;
        foreach($hoadon as $hd)
        {
            
        }
        $khachhang = KhachHang::where('customer_id',$customer_id)->first();
        $shipping = GiaoHang::where('shipping_id',$shipping_id)->first();
        $payment = ThanhToan::where('payment_id',$payment_id)->first();
        return view('admin.donhang.chitiet',compact('chitiethoadon','khachhang','shipping','payment'));
    }
    public function getInDonHang($checkout_code){
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($this->print_order_convert($checkout_code));
        return $pdf->stream();
    }
    public function print_order_convert($checkout_code){
        $chitiethoadon = ChiTietHoaDon::where('order_code',$checkout_code)->get();
        $hoadon = HoaDon::where('order_code',$checkout_code)->get();
        foreach($hoadon as $hd)
        {
            $customer_id = $hd->customer_id;
            $shipping_id = $hd->shipping_id;
            $payment_id = $hd->payment_id;
        }
        $khachhang = KhachHang::where('customer_id',$customer_id)->first();
        $shipping = GiaoHang::where('shipping_id',$shipping_id)->first();
        $payment = ThanhToan::where('payment_id',$payment_id)->first();
        $output='';

        $output .='
            <style>
            body{
                font-family:DejaVu Sans;


            }
            .table-styling{
                border-collapse: collapse;
                border: 1px solid black;
               width:700px;
                border-spacing: 0;
                text-align:center;
            }
           .table-styling th{
            border-right: 1px solid black;
            text-align:center;
            }
            .table-styling tr td{
                border: 1px solid black;
            }
            </style>
            <h2><center>C???a H??ng ??i???n Tho???i HKT SHOP MOBILE</center></h2>
            <p>TH??NG TIN ?????T H??NG</p>
        <table class="table-styling">
                        <thead>
                            <tr>
                                    <th>T??n kh??ch h??ng</th>
                                    <th>S??? ??i???n tho???i</th>
                                    <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>';
                        
    $output.='
                            <tr>
                                <td>'.$khachhang->customer_name.' </td>
                                <td>'.$khachhang->customer_phone.' </td>
                                <td>'.$khachhang->customer_email.' </td>
                            </tr>';
    $output.='
                        </tbody>
        </table>
        <p>TH??NG TIN V???N CHUY???N</p>
        <table class="table-styling">
                        <thead>
                            <tr>
                                    <th>T??n ng?????i nh???n</th>
                                    <th>S??? ??i???n tho???i</th>
                                    <th>?????a ch???</th>
                                    <th>Ghi ch?? giao h??ng</th>
                            </tr>
                        </thead>
                        <tbody>';
                        
    $output.='
                            <tr>
                                <td>'. $shipping->shipping_name.' </td>
                                <td>'. $shipping->shipping_phone.' </td>
                                <td>'. $shipping->shipping_address.' </td>
                                <td>'. $shipping->shipping_notes.' </td>
                            </tr>';
    $output.='
                        </tbody>
        </table>
        <p>S???N PH???M MUA</p>
        <table class="table-styling">
                        <thead>
                            <tr>
                                    <th>T??n s???n ph???m</th>
                                    <th>Gi??</th>
                                    <th>Ph?? giao h??ng</th>
                                    <th>S??? l?????ng</th>
                                    <th>T???ng ti???n</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $tong=  0;
                        
                        
                        foreach($chitiethoadon as $sp){
                            $subtotal = $sp->product_price*$sp->product_sales_quantity;
                            $tong += $subtotal;
    $output.='
                            <tr>
                                <td>'. $sp->product_name.' </td>
                                <td>'. number_format($sp->product_price).'??'.' </td>
                                <td>'. $sp->product_ok.' </td>
                                <td>'. $sp->product_feeship.'??</td>
                                <td>'. $sp->product_sales_quantity.' </td>
                                <td>'. number_format($subtotal).'??'.' </td>
                            </tr>';
                        }
                       
    $output.='
                        <tr>
                            <td colspan="6">
                                <p>Ph?? ship:'.number_format($sp->product_feeship).'??  </p>
                                <p>Thanh to??n: '. number_format($tong +$sp->product_feeship).'??'.' </p>
                            </td>
                        </tr>
    ';
    $output.='
                        </tbody>
        </table>
        
        <table>
                        <thead>
                            <tr>
                            <th width="200px">Ng?????i l???p phi???u</th>
                            <th width="800px">Ng?????i nh???n</th>
                            </tr>
                        </thead>
                        <tbody>';
    $output.='                  <tr>
                                    
                                    <td align="center">'.$shipping->created_at->format('d/m/Y').'<br>K?? t??n</td>
                                    <td align="center">'. $shipping->shipping_name.'</td>
                                </tr>';
    $output.='
                        </tbody>
        </table>
        ';
        return $output;
    }
    public function getEdit($order_id)
    {
        $order = HoaDon::find($order_id);
        return view('admin.donhang.sua',compact('order'));
    }
    public function postEdit(Request $request,$order_id)
    {
        $this->validate($request,
        [
            'TrangThai'=>'required'
        ],
        [
            'TrangThai.required'=>'B???n ch??a l???a ch???n tr???ng th??i'
        ]
        );
        $hoadon = HoaDon::find($order_id);
        $hoadon->order_status = $request->TrangThai;
        $hoadon->save();

        return redirect('admin/donhang/danhsach')->with('thongbao','Ch???nh s???a tr???ng th??i ????n h??ng '.$hoadon->order_code.' th??nh c??ng');
    }
    
}
