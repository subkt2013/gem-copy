<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\Corporation;
use App\Models\Course;
use App\Models\Html;
use App\Models\Bank;
use App\Models\Tag;
use App\Http\Requests\EquityProjectRequest;
use Illuminate\Support\Facades\DB;

class EquityController extends Controller
{
    /**
     * Equity一覧表示
     */
    //DBから一覧を出力　モデルを使う
    public function index()
    {
        $projects = Project::orderBy('id','desc')->paginate(20);

        //セッションへデータを保存する paginateはクエリビルダに統合されている ?
        //session(['project_session' => $projects->currentPage()]);

        return view('equity.project.index',['projects'=>$projects]);
    }

    /**
     * Equity詳細表示
     */
    public function show($id)
    {
        $project = Project::find($id);

        //ドキュメント名の追加? explode→区切り　collect->last()→最後のパスを返す
        //$project->pre_contract_document_name = collect(explode('/', $project->pre_contract_document))->last();
        //$project->wanted_matters_notice_name = collect(explode('/', $project->wanted_matters_notice))->last();

        return view('equity.project.show', ['project' => $project]);

    }

    /**
     * Equity新規作成ページ表示
     */
    public function create(Request $request)
    {
        return view('equity.project.create', $params);
    }

    /**
     * Equity新規作成処理 POST
     */
    public function store(EquityProjectRequest $request)
    {

    }

    /**
     * Equity更新ページ表示
     */
    public function edit($id)
    {
        return view('equity.project.edit', $params);
    }

    /**
     * Equity更新処理
     */
    public function update(EquityProjectRequest $request, $id)
    {
        $project_id = $id;
        $corporation_id = Project::find($project_id)->corporation_id;
        $bank_id = Project::find($project_id)->bank_id;

        $data = $request->all();

        //日付データの整形してdataに戻す
        if(isset($data['release_datetime'])){
            $release_datetime = new Carbon($data['release_datetime']);
            $data['release_datetime'] = $release_datetime->toDateTimeString();
        }

        if(isset($data['recruit_start_datetime'])){
            $recruit_start_datetime = new Carbon($data['recruit_start_datetime']);
            $data['recruit_start_datetime'] = $recruit_start_datetime->toDateTimeString();
        }

        if(isset($data['scheduled_end_datetime'])){
            $scheduled_end_datetime = new Carbon($data['scheduled_end_datetime']);
            $data['scheduled_end_datetime'] = $scheduled_end_datetime->toDateTimeString();
        }
        
        $transfer_date = new Carbon($data['transfer_date']);
        $data['transfer_date'] = $transfer_date->toDateTimeString();

        $payment_due_date = new Carbon($data['payment_due_date']);
        $data['payment_due_date'] = $payment_due_date->toDateTimeString();

        //株数2,3に利用しないチェックが入っている場合、値を空にする
        if($request->_course2 === 'on') $data['course2'] = '';
        if($request->_course3 === 'on') $data['course3'] = '';
        
        // コーポレートデータ
        $corp_data = [];
        $corp_data['name'] = $request->corporation_name;
        $corp_data['representative'] = $request->representative;
        $corp_data['position'] = $request->position;

        // 銀行データ
        $bank_data = [];
        $bank_data['bank'] = $request->bank;
        $bank_data['branch'] = $request->branch;
        $bank_data['type'] = $request->type;
        $bank_data['number'] = $request->number;
        $bank_data['receiver_kana'] = $request->receiver_kana;

        //tagsデータ
        $tag_data = explode(",", $request->tag);

        //コースデータ
        $courses = [$request->course1,0,0];
        if($request->course2) $courses[1] = $request->course2;
        if($request->course3) $courses[2] = $request->course3;
        $course_ids = Project::find($project_id)->hasCourses->pluck('id')->all();
        $course_data = [];
        foreach ($courses as $key => $course) {
            $course_data += [$key => ['course_id' => $course_ids[$key], 'number_of_shares' => $course]];  
        }

        //DB処理開始
        DB::beginTransaction();
        try {

            // Project本体の更新
            Project::find($project_id)->fill($data)->save();

            //courseの保存
            $recruit_started_flg = Project::find($project_id)->recruit_start_datetime->lte(Carbon::now());
            foreach ($course_data as $key => $course_datum) {
                //募集開始日を過ぎていた場合
                if($recruit_started_flg) {
                    //コース1は無条件でなにもしない
                    //コース2&3は現在の値が0なら保存する
                    $course_amount = Course::find($course_datum['course_id'])->number_of_shares;
                    if(($key === 1 && $course_amount === 0) || ($key === 2 && $course_amount === 0)){
                        Course::find($course_datum['course_id'])->fill(['number_of_shares' => $course_datum['number_of_shares']])->save();
                    }
                }else{
                    Course::find($course_datum['course_id'])->fill(['number_of_shares' => $course_datum['number_of_shares']])->save();
                }
            }

            // corpの更新
            if($request->corporation_id_check!=1){
                Corporation::find($corporation_id)->fill($corp_data)->save();
            }

            // bankの更新
            Bank::find($bank_id)->fill($bank_data)->save();

            // tagsの保存
            $tags_ids = [];
            foreach ($tag_data as $key => $tag) {
                $tag_insert = Tag::firstOrCreate(['name' => $tag]);
                $tags_ids += [$key => $tag_insert->id];
            }

            // tagsのリレーション
            Project::find($project_id)->tags()->sync($tags_ids);

            // file upload
            $img_path = 'img/projects/';

            //プロジェクトに関わるファイルを更新する
            $pj_updata = [];
            if($request->hasFile('thumbnail')) $pj_updata['thumbnail'] = 'storage/'.$request->thumbnail->storeAs($img_path.$project_id, 'thumbnail.'.$request->thumbnail->getClientOriginalExtension(), 'public');
            if($request->hasFile('pre_contract_document')) $pj_updata['pre_contract_document'] = 'storage/'.$request->pre_contract_document->storeAs($img_path.$project_id, 'pre_contract_document.'.$request->pre_contract_document->getClientOriginalExtension(), 'public');
            if($request->hasFile('wanted_matters_notice')) $pj_updata['wanted_matters_notice'] = 'storage/'.$request->wanted_matters_notice->storeAs($img_path.$project_id, 'wanted_matters_notice.'.$request->wanted_matters_notice->getClientOriginalExtension(), 'public');
            if($request->hasFile('convocation_notification')) $pj_updata['convocation_notification'] = 'storage/'.$request->convocation_notification->storeAs($img_path.$project_id, 'convocation_notification.'.$request->convocation_notification->getClientOriginalExtension(), 'public');
            Project::find($project_id)->fill($pj_updata)->save();

            //corpに関わるファイルを更新する
            if($request->corporation_id_check!=1){
                $cp_updata = [];
                if($request->hasFile('icon')) $cp_updata['icon'] = 'storage/'.$request->icon->storeAs($img_path.$project_id, 'icon.'.$request->icon->getClientOriginalExtension(), 'public');
                if($request->hasFile('logo')) $cp_updata['logo'] = 'storage/'.$request->logo->storeAs($img_path.$project_id, 'logo.'.$request->logo->getClientOriginalExtension(), 'public');
                if($request->hasFile('image')) $cp_updata['image'] = 'storage/'.$request->image->storeAs($img_path.$project_id, 'image.'.$request->image->getClientOriginalExtension(), 'public');
                Corporation::find($corporation_id)->fill($cp_updata)->save();
            }

            //二重クリック対策
            $request->session()->regenerateToken();

            DB::commit();

            return redirect()->route('equity.project.complete', ['id' => $project_id]);

        } catch (\Exception $e) {
            //二重クリック対策
            $request->session()->regenerateToken();
            
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Equity削除処理
     */
    public function destroy($id)
    {
        // project本体の論理削除(model側でSoftDeletesになっていれば論理削除される)
        $project = Project::find($id);
        if($project->status === 0){
            $project->delete();
            return redirect()->route('equity.project.complete', ['id' => $id]);
        }else{
            return redirect()->route('equity.project.index')->with('flash_message', 'プロジェクトステータスが[準備中]以外であった為削除できませんでした。');
        }
        
    }

    /**
     * Equity完了
     */
    public function complete($id)
    {
        return view('equity.project.complete');
    }
}