<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormat;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Exception;
// use Illuminate\Contracts\Support\ValidatedData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
// use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            // Ambil seluruh data
            $transactions = Transaction::all();
            // Jika data belum ada kembelikan response didalam if
            if (!$transactions->count() > 0) {
                return ResponseFormat::createResponse(404, 'Data belum tersedia');
            }
        } catch (Exception $e) {
            return ResponseFormat::createResponse(500, 'Something went wrong on index POS', $e->getMessage());
        }

        return ResponseFormat::createResponse(200, 'Ok', $transactions);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $product = Product::find(1);
        // return $product->stock < 1;

        // Validasi input
        $validator = $this->validateTransaction();

        // Jika ada input yang tidak sesuai kirimkan ERRORnya
        if ($validator->fails()) {
            return ResponseFormat::createResponse(400, 'Format data yang dikirim salah', $validator->errors());
        }

        $validatedData = $validator->validated();   
        
        // Data transaksi
        $transactionValue = [
            'code' => 'TRX' .  mt_rand(1,100).substr(time(), 6, 6),
            'method' => 'Tunai',
            'total_kuantitas' => array_sum($validatedData['jumlah']),
            'total_harga' => 0,
            'diterima' => $validatedData['diterima'],
            'kembalian' => 0,
        ];

        // Cek $validatedData['product_id'] apakah produk id terdapat pada database
        foreach ($validatedData['product_id'] as $id) {
            if (Product::find($id) == null) {
                return ResponseFormat::createResponse(404, 'Product id tidak ditemukan', $id);
            }
        }

        // Perhitungan untuk melengkapi $transactionValue
        $index = 0;
        foreach ($validatedData['product_id'] as $id) {
            $product = Product::findOrFail($id);
            $totalHargaProduct = $product->harga_jual * $validatedData['jumlah'][$index];
            $transactionValue['total_harga'] += $totalHargaProduct;
        }
        
        // Jika kembalian < 0 / minus maka kembalikan error bahwa uang kurang
        $transactionValue['kembalian'] = $transactionValue['diterima'] - $transactionValue['total_harga'];
        if ($transactionValue['kembalian'] < 0) {
            $data = [
                'uang_diterima' => intval($transactionValue['diterima']),
                'total_pembayaran' => $transactionValue['total_harga']
            ];
            DB::rollBack();
            return ResponseFormat::createResponse(400, 'Uang kurang', $data);
        }

        try {
            // Start Transaction
            $transactionDB = DB::transaction(function () use ($transactionValue, $validatedData) {
                // Simpan $transactionValue ke DB
                $transaction = Transaction::create($transactionValue);
                $index = 0;
                foreach ($validatedData['product_id'] as $id ) {
                    $product = Product::findOrFail($id);
                    // Mendapatkan toal harga barang
                    $totalHarga = $product->harga_jual * $validatedData['jumlah'][$index];
                    // Data detail transaksi
                    $transactionDetails = [
                        'product_id' => $id,
                        'transaction_id' => $transaction->id,
                        'harga_beli' => $product->harga_beli,
                        'harga_jual' => $product->harga_jual,
                        'kuantitas' => $validatedData['jumlah'][$index],
                        'total' => $totalHarga
                    ];

                    // Cek jika stok kurang dari permintaan makan kirimkan error Stock kurang
                    if ($validatedData['jumlah'][$index] > $product->stock) {
                        $data = [
                            'nama_barang' => $product->title,
                            'request_stock' => $validatedData['jumlah'][$index],
                            'ready_stock' => $product->stock
                        ];
                        return ResponseFormat::createResponse(400, 'Stock kurang', $data);
                    }

                    // Simpan $transactionDetails (Detail transaksi)
                    $orderItem = TransactionDetail::create($transactionDetails);

                    // Update stock
                    if ($orderItem) {
                        $product->stock -= $validatedData['jumlah'][$index];
                        $product->save();
                    }

                    $index += 1;
                }

                // Data untuk nilai kembalian
                $data = [
                    'Kembalian' => $transactionValue['kembalian'],
                ];
                return ResponseFormat::createResponse(201, 'Ok', $data);
            });
        } catch (Exception $e) {
            return ResponseFormat::createResponse(500, 'Something went wrong on store POS', $e->getMessage());
        }

        return $transactionDB;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        $transactionDetail = [
            'transaction' => $transaction,
            'transaction_detail' => TransactionDetail::where('transaction_id', $transaction->id)->get()
        ];
        return ResponseFormat::createResponse(200, 'Ok', $transactionDetail);
        // return dd($transaction->transaction_details());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        //
    }

    // Validasi data
    public function validateTransaction(){
        return Validator::make(request()->all(), [
            'product_id' => 'required|array|min:1', 
            'jumlah' => 'required|array|min:1',
            'product_id.*' => 'required|integer|gt:0',
            'jumlah.*' => 'required|integer|gt:0',
            'diterima' => 'required|integer|gt:0',
        ]);
    }
}
