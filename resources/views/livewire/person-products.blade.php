<div>

    {{-- DaisyUI Drawer for Product Creation --}}
    <div class="drawer drawer-end" x-data="{ open: @entangle('showProductForm') }">
        <input id="product-create-drawer" type="checkbox" class="drawer-toggle" x-model="open" />
        <div class="drawer-content">
            <!-- Page content here -->
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold">Products List</h2>
                    <div class="flex gap-2">
                        <button class="btn btn-outline btn-sm">Import</button>
                        <button class="btn btn-outline btn-sm">Export</button>
                        <label for="product-create-drawer" class="drawer-button btn btn-primary btn-sm"
                            @click="$wire.showProductForm()">+ Add Product</label>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mb-4">
                    <div class="form-control">
                        <input type="text" placeholder="Search" class="input input-bordered input-sm w-48" />
                    </div>
                    <div class="form-control">
                        <input type="text" class="input input-bordered input-sm w-56"
                            placeholder="12 Sep - 28 Oct 2024" />
                    </div>
                    <select class="select select-bordered select-sm w-32">
                        <option>Status</option>
                        <option>Published</option>
                        <option>Inactive</option>
                        <option>Draft</option>
                    </select>
                    <select class="select select-bordered select-sm w-32">
                        <option>Category</option>
                        <option>Clothes</option>
                        <option>Beauty</option>
                        <option>Electronic</option>
                    </select>
                    <button class="btn btn-outline btn-sm">Filter</button>
                </div>
                <div class="overflow-x-auto rounded-lg shadow bg-base-100">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th><input type="checkbox" class="checkbox checkbox-xs" /></th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Stock</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="checkbox" class="checkbox checkbox-xs" /></td>
                                <td class="flex items-center gap-2"><img
                                        src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRRyBsbHGAUufL50jjknNn_L3NKcN9DdXbxhg&s" class="w-8 h-8 rounded"
                                        alt="" /> Casual Sunglass</td>
                                <td>Sunglass</td>
                                <td><span class="text-warning">124 Low Stock</span></td>
                                <td>25,000 Ugshs</td>
                                <td><span class="badge badge-success badge-outline">Published</span></td>
                                <td><button class="btn btn-ghost btn-xs">...</button></td>
                            </tr>
                            <!-- ...other static rows... -->
                        </tbody>
                    </table>
                    {{-- <div class="flex items-center justify-between p-4">
                        <div>Result 1-10 of 45</div>
                        <div class="flex items-center gap-2">
                            <select class="select select-bordered select-xs w-16">
                                <option>10</option>
                                <option>20</option>
                                <option>50</option>
                            </select>
                            <div class="join">
                                <button class="join-item btn btn-xs">&lt; Previous</button>
                                <button class="join-item btn btn-xs btn-active">1</button>
                                <button class="join-item btn btn-xs">2</button>
                                <button class="join-item btn btn-xs">3</button>
                                <button class="join-item btn btn-xs">...</button>
                                <button class="join-item btn btn-xs">12</button>
                                <button class="join-item btn btn-xs">Next &gt;</button>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
        <div class="drawer-side">
            <label for="product-create-drawer" aria-label="close sidebar" class="drawer-overlay" @click="$wire.hideProductForm()"></label>
            <div class="bg-base-100 min-h-full w-[600px] p-6 flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Add New Product</h3>
                    <label for="product-create-drawer" class="btn btn-sm btn-circle btn-ghost" @click="$wire.hideProductForm()">✕</label>
                </div>
                <form wire:submit.prevent="createProduct" class="space-y-6 flex-1 overflow-y-auto">
                    <!-- 1. Basic Product Information -->
                    <div>
                        <div class="font-bold text-base mb-2">1. Basic Product Information</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="form-control">
                                <label class="label">Product Name</label>
                                <input type="text" wire:model.defer="productForm.product_name" class="input input-bordered w-full" required />
                                @error('productForm.product_name') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label">SKU</label>
                                <input type="text" wire:model.defer="productForm.sku" class="input input-bordered w-full" required />
                                @error('productForm.sku') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label">Product Code / Barcode / QR Code</label>
                                <input type="text" wire:model.defer="productForm.product_code" class="input input-bordered w-full" />
                                @error('productForm.product_code') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label">Slug</label>
                                <input type="text" wire:model.defer="productForm.slug" class="input input-bordered w-full" />
                                @error('productForm.slug') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control md:col-span-2">
                                <label class="label">Description</label>
                                <textarea wire:model.defer="productForm.description" class="textarea textarea-bordered w-full"></textarea>
                                @error('productForm.description') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control md:col-span-3">
                                <label class="label">Short Description</label>
                                <textarea wire:model.defer="productForm.short_description" class="textarea textarea-bordered w-full"></textarea>
                                @error('productForm.short_description') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 2. Pricing & Offers -->
                    <div>
                        <div class="font-bold text-base mb-2">💰 2. Pricing & Offers</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="form-control">
                                <label class="label">Price</label>
                                <input type="number" wire:model.defer="productForm.price" class="input input-bordered w-full" required min="0" step="0.01" />
                                @error('productForm.price') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label">Discount Price / Sale Price</label>
                                <input type="number" wire:model.defer="productForm.discount_price" class="input input-bordered w-full" min="0" step="0.01" />
                                @error('productForm.discount_price') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label">Currency</label>
                                <input type="text" wire:model.defer="productForm.currency" class="input input-bordered w-full" placeholder="USD" />
                                @error('productForm.currency') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label">Tax Rate (%)</label>
                                <input type="number" wire:model.defer="productForm.tax_rate" class="input input-bordered w-full" min="0" max="100" step="0.01" />
                                @error('productForm.tax_rate') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label">Discount Type</label>
                                <select wire:model.defer="productForm.discount_type" class="select select-bordered w-full">
                                    <option value="">Select type</option>
                                    <option value="percentage">Percentage</option>
                                    <option value="fixed">Fixed Amount</option>
                                </select>
                                @error('productForm.discount_type') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label">Discount Start</label>
                                <input type="date" wire:model.defer="productForm.discount_start" class="input input-bordered w-full" />
                                @error('productForm.discount_start') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label">Discount End</label>
                                <input type="date" wire:model.defer="productForm.discount_end" class="input input-bordered w-full" />
                                @error('productForm.discount_end') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 3. Inventory & Stock Management -->
                    <div>
                        <div class="font-bold text-base mb-2">📦 3. Inventory & Stock Management</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="form-control">
                                <label class="label">Quantity / Stock</label>
                                <input type="number" wire:model.defer="productForm.quantity" class="input input-bordered w-full" min="0" />
                                @error('productForm.quantity') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label">Stock Status</label>
                                <select wire:model.defer="productForm.stock_status" class="select select-bordered w-full">
                                    <option value="">Select status</option>
                                    <option value="in_stock">In Stock</option>
                                    <option value="out_of_stock">Out of Stock</option>
                                    <option value="backordered">Backordered</option>
                                </select>
                                @error('productForm.stock_status') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label">Reorder Level</label>
                                <input type="number" wire:model.defer="productForm.reorder_level" class="input input-bordered w-full" min="0" />
                                @error('productForm.reorder_level') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control md:col-span-3">
                                <label class="label">Warehouse Location</label>
                                <input type="text" wire:model.defer="productForm.warehouse_location" class="input input-bordered w-full" />
                                @error('productForm.warehouse_location') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 4. Categorization & Classification -->
                    <div>
                        <div class="font-bold text-base mb-2">🏷️ 4. Categorization & Classification</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="form-control">
                                <label class="label">Category</label>
                                <input type="text" wire:model.defer="productForm.category_id" class="input input-bordered w-full" />
                                @error('productForm.category_id') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label">Subcategory</label>
                                <input type="text" wire:model.defer="productForm.subcategory_id" class="input input-bordered w-full" />
                                @error('productForm.subcategory_id') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label">Brand</label>
                                <input type="text" wire:model.defer="productForm.brand_id" class="input input-bordered w-full" />
                                @error('productForm.brand_id') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control md:col-span-2">
                                <label class="label">Tags (comma separated)</label>
                                <input type="text" wire:model.defer="productForm.tags" class="input input-bordered w-full" placeholder="wireless, bluetooth, audio" />
                                @error('productForm.tags') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label">Product Type</label>
                                <select wire:model.defer="productForm.product_type" class="select select-bordered w-full">
                                    <option value="">Select type</option>
                                    <option value="simple">Simple</option>
                                    <option value="variable">Variable</option>
                                    <option value="bundle">Bundle</option>
                                    <option value="service">Service</option>
                                </select>
                                @error('productForm.product_type') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 5. Media & Files -->
                    <div>
                        <div class="font-bold text-base mb-2">🖼️ 5. Media & Files</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="form-control">
                                <label class="label">Thumbnail / Main Image</label>
                                <input type="file" wire:model="productForm.thumbnail" class="file-input file-input-bordered w-full" />
                                @error('productForm.thumbnail') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control md:col-span-2">
                                <label class="label">Gallery Images</label>
                                <input type="file" wire:model="productForm.gallery_images" class="file-input file-input-bordered w-full" multiple />
                                @error('productForm.gallery_images') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control md:col-span-2">
                                <label class="label">Video URL</label>
                                <input type="url" wire:model.defer="productForm.video_url" class="input input-bordered w-full" />
                                @error('productForm.video_url') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label">Spec Sheet / Manual (PDF)</label>
                                <input type="file" wire:model="productForm.spec_sheet" class="file-input file-input-bordered w-full" accept="application/pdf" />
                                @error('productForm.spec_sheet') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 6. Shipping & Dimensions -->
                    <div>
                        <div class="font-bold text-base mb-2">🚚 6. Shipping & Dimensions</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="form-control">
                                <label class="label">Weight</label>
                                <input type="number" wire:model.defer="productForm.weight" class="input input-bordered w-full" min="0" step="0.01" />
                                @error('productForm.weight') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label">Dimensions (L×W×H)</label>
                                <input type="text" wire:model.defer="productForm.dimensions" class="input input-bordered w-full" placeholder="25×20×10 cm" />
                                @error('productForm.dimensions') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control">
                                <label class="label">Shipping Class</label>
                                <input type="text" wire:model.defer="productForm.shipping_class_id" class="input input-bordered w-full" />
                                @error('productForm.shipping_class_id') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-control flex items-center gap-2 md:col-span-3">
                                <input type="checkbox" wire:model.defer="productForm.free_shipping" class="checkbox" id="free_shipping" />
                                <label for="free_shipping" class="label cursor-pointer">Free Shipping</label>
                                @error('productForm.free_shipping') <span class="text-error text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 7. Vendor & Supplier -->
                    <div>
                        <div class="font-bold text-base mb-2">Vendor & Supplier</div>
                        <div class="p-4 bg-base-200 rounded text-sm text-gray-600">
                            Vendor and Supplier information is auto-captured and does not require manual entry.
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary">Create Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
