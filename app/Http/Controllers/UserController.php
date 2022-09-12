<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use \Illuminate\Http\JsonResponse;

class UserController extends Controller {
    protected $cipher;
    protected $key;
    protected $signature;
    protected $iv_length;
    protected $iv_encode;
    protected $tag_length;
    protected $tag;

    public function __construct() {
        $this->cipher       = 'aes-256-gcm';
        $this->key          = env('PASSWORD');
        $this->signature    = substr(hash('sha256', $this->key, true), 0, 32);
        $this->iv_length    = openssl_cipher_iv_length($this->cipher);
        $this->tag_length   = 16;
        $this->iv_encode    = openssl_random_pseudo_bytes($this->iv_length);
        $this->tag          = '';
    }

    public function index () {
        $user           = User::find(auth()->user()->id);
        $user->photo    = self::imageDecode($user->photo);

        return view('profile.user', compact('user'));
    }

    public function storePhoto(Request $request, $id): JsonResponse {
        try {
            $user           = User::findOrFail($id);
            $user->photo    = self::imageEncode($request->photo);
            $user->save();

            return response()->json(['message' => 'Photo uploaded successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function showPhoto($id): JsonResponse {
        try {
            $user   = User::findOrFail($id);
            $photo  = self::imageDecode($user->photo);

            return response()->json(['photo' => $photo], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function imageEncode($image): string {
        // Convert to String
        $image
            = 'data:image/'
            . $image->getClientOriginalExtension()
            . ';base64,' . base64_encode(file_get_contents($image));

        // Encrypt to AES-256-GCM
        $encrypted
            = openssl_encrypt(
                $image,
                $this->cipher,
                $this->signature,
                OPENSSL_RAW_DATA,
                $this->iv_encode,
                $this->tag,
                '',
                $this->tag_length
            );

        // Encode to Base64
        return base64_encode($this->iv_encode . $encrypted . $this->tag);
    }

    private function imageDecode($image): string {
        // Decode from Base64
        $image = base64_decode($image);

        // Decrypt from AES-256-GCM
        $iv_decode  = substr($image, 0, $this->iv_length);
        $chipertext = substr($image, $this->iv_length, -$this->tag_length);
        $tag        = substr($image, -$this->tag_length);

        // Open SSL Decrypt
        return openssl_decrypt(
                $chipertext,
                $this->cipher,
                $this->signature,
                OPENSSL_RAW_DATA,
                $iv_decode,
                $tag
            );
    }
}
