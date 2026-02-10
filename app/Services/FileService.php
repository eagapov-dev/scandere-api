<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public function storeProduct(UploadedFile $file): array
    {
        $path = $file->store('products', 'private');

        return [
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientOriginalExtension(),
        ];
    }

    public function deleteProduct(Product $product): void
    {
        if ($product->file_path) {
            Storage::disk('private')->delete($product->file_path);
        }
    }

    public function secureDownload(Product $product)
    {
        if (!$product->file_path || !Storage::disk('private')->exists($product->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('private')->download($product->file_path, $product->file_name);
    }
}
