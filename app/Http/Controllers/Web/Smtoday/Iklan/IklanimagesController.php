<?php

namespace Vanguard\Http\Controllers\Web\Smtoday\Iklan;

use Exception;

use Illuminate\Http\Request;
use App\Http\Requests;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vanguard\Http\Controllers\Controller;


use Vanguard\Iklanimage;
use Vanguard\Repositories\Smtoday\Iklanimage\IklanimageRepository;
use Vanguard\Support\Enum\IklantextStatus;

use Vanguard\Http\Requests\Smtoday\Iklanimage\CreateIklanimageRequest;
use Vanguard\Http\Requests\Smtoday\Iklanimage\UpdateIklanimageRequest;
use Vanguard\Events\Smtoday\Iklantext\Deleted;

use App\Image_uploaded;
use Carbon\Carbon;
use Image;
use File;


/**
 * Class IklanimagesController
 * @package Vanguard\Http\Controllers
 */
class IklanimagesController extends Controller
{
    public $path;
    public $dimensions;
    /**
     * @var IklanimagesRepository
     */
    private $iklanimages;

    /**
     * IklanimagesController constructor.
     * @param IklanimagesRepository $iklanimages
     */
    public function __construct(IklanimageRepository $iklanimages)
    {
        $this->iklanimages = $iklanimages;

        //DEFINISIKAN PATH
        $this->path = public_path('/upload/smtoday/iklanimage');
        //DEFINISIKAN DIMENSI
        $this->dimensions = ['1080'];
    }

    /**
     * Displays the page with all available iklanimages.
     *
     * @return Factory|View
     */
    public function index(Request $request)
    {
        $iklanimages = $this->iklanimages->paginate($perPage = 10, $request->search, $request->status);

        $statuses = ['' => __('All')] + IklantextStatus::lists();

        return view('smtoday.iklanimage.index', compact('iklanimages', 'statuses'));
    }

    /**
     * Displays the form for creating new iklanimage.
     *
     * @return Factory|View
     */
    public function create()
    {
        return view('smtoday.iklanimage.add', [
            'statuses' => IklantextStatus::lists(),
            'edit' => false
        ]);
    }


    /**
     * Store Iklanimage to database.
     *
     * @param CreateIklanimageRequest $request
     * @return mixed
     */

    public function store(CreateIklanimageRequest $request)
    {
        // When iklantext is created, we will set his
        // status to Unsend by default.
        // database default falue
        $this->validate($request, [
            'image' => 'required|image|mimes:jpg,png,jpeg'
        ]);

        //JIKA FOLDERNYA BELUM ADA
        if (!File::isDirectory($this->path)) {
            //MAKA FOLDER TERSEBUT AKAN DIBUAT
            File::makeDirectory($this->path);
        }

        //MENGAMBIL FILE IMAGE DARI FORM
        $file = $request->file('image');
        //MEMBUAT NAME FILE DARI GABUNGAN TIMESTAMP DAN UNIQID()
        $fileName = Carbon::now()->timestamp . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        //UPLOAD ORIGINAN FILE (BELUM DIUBAH DIMENSINYA)
        //Image::make($file)->save($this->path . '/' . $fileName);

        //LOOPING ARRAY DIMENSI YANG DI-INGINKAN
        //YANG TELAH DIDEFINISIKAN PADA CONSTRUCTOR
        foreach ($this->dimensions as $row) {
            //MEMBUAT CANVAS IMAGE SEBESAR DIMENSI YANG ADA DI DALAM ARRAY
            $canvas = Image::canvas($row, $row);
            //RESIZE IMAGE SESUAI DIMENSI YANG ADA DIDALAM ARRAY
            //DENGAN MEMPERTAHANKAN RATIO
            $resizeImage  = Image::make($file)->resize($row, $row, function($constraint) {
                $constraint->aspectRatio();
            });

            //CEK JIKA FOLDERNYA BELUM ADA
            if (!File::isDirectory($this->path . '/' . $row)) {
                //MAKA BUAT FOLDER DENGAN NAMA DIMENSI
                File::makeDirectory($this->path . '/' . $row);
            }

            //MEMASUKAN IMAGE YANG TELAH DIRESIZE KE DALAM CANVAS
            $canvas->insert($resizeImage, 'center');
            //SIMPAN IMAGE KE DALAM MASING-MASING FOLDER (DIMENSI)
            $canvas->save($this->path . '/' . $row . '/' . $fileName);
        }

        //SIMPAN DATA IMAGE YANG TELAH DI-UPLOAD
        // Image_uploaded::create([
        //     'name' => $fileName,
        //     'dimensions' => implode('|', $this->dimensions),
        //     'path' => $this->path
        // ]);

        // Iklanimage::create([
        //     'nama' => $request->nama,
        //     'image' => $fileName,
        //     'biaya' => $request->biaya,
        //     'nomor_va' => $request->nomor_va,
        //     'status' => $request->status
        // ]);

        $data = ([
            'judul' => $request->judul,
            'image' => $fileName,
            'status' => IklantextStatus::UNSEND
        ]);

        // $data = $request->all() + [
        // ];
        $this->iklanimages->create($data);

        return redirect()->route('iklanimage.index')
            ->withSuccess(__('Iklan created successfully.'));
    }

    /**
     * Displays the form for editing specific Iklanimage.
     *
     * @param Iklantext $iklanimage
     * @return Factory|View
     */

    public function edit(Iklanimage $iklanimage)
    {
        return view('smtoday.iklanimage.add', [
            'edit' => true,
            'iklanimage' => $iklanimage,
            'statuses' => IklantextStatus::lists()
        ]);
    }

    /**
     * Update specified iklanimage.
     *
     * @param Iklanimage $iklanimage
     * @param UpdateIklanimageRequest $iklanimage
     * @return mixed
     */
    public function update(Iklanimage $iklanimage, UpdateIklanimageRequest $request)
    {
        $this->iklanimages->update($iklanimage->id, $request->all());

        return redirect()->route('iklanimage.index')
            ->withSuccess(__('Iklan updated successfully.'));
    }

    /**
     * Displays iklan image page.
     *
     * @param User $iklanimage
     * @return Factory|View
     */
    public function show(Iklanimage $iklanimage)
    {
        return view('smtoday.iklanimage.view', compact('iklanimage'));
    }


    /**
     * Destroy the Iklanimage if it is removable.
     *
     * @param Iklanimage $iklanimage
     * @return mixed
     * @throws Exception
     */
    public function destroy(Iklanimage $iklanimage)
    {

        $this->iklanimages->delete($iklanimage->id);

        return redirect()->route('iklanimage.index')
            ->withSuccess(__('Iklan deleted successfully.'));
    }
}
