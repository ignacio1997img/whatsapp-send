<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Http;

// Models
use App\Models\Contact;
use App\Models\Server;
use App\Models\Message;

// Queue
use App\Jobs\ProcessSendMessage;
use PhpParser\Node\Stmt\Return_;

class SenderController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }
    
    public function index() {
        $this->custom_authorize('browse_sender');
        return view('dashboard.browse');
    }

    public function send(Request $request) {
        try {
            // return $request;
            $contact_id = $request->contact_id;
            $image = $this->save_image($request->file('image'), 'messages');
            // dd($image);

            if($contact_id[0] == 'todos'){
                $contacts = Contact::where('status', 1)->where('deleted_at', null)->get();
            }else{
                $contacts = Contact::whereIn('id', $contact_id)->get();
            }
            // return $contacts;
            $server = Server::where('status', 1)->first();
            // return $server;
            if ($server) {
                foreach ($contacts as $contact) {
                    $new_message = Message::create([
                        'user_id' => Auth::user()->id,
                        'contact_id' => $contact->id,
                        'text' => $request->message,
                        'image' => $image,
                        'server_id'=>$server->id
                    ]);

                    $message = Message::find($new_message->id);
                    // return $message;
                    return $server->url.'/send';


                    Http::post($server->url.'/send', [
                        'phone' => strlen($message->contact->phone) == 8 ? '591'.$message->contact->phone : $message->contact->phone,
                        'text' => url('storage/'.$message->image),
                        // 'image' => $message->image ? url('storage/'.$message->image) : '',asset('storage/'.$requirement->ci)
                        'image' => $message->image ? url('storage/app/public/'.$message->image):'',
                    ]);
                    // return 1;
                    // ProcessSendMessage::dispatch($message);
                }
            }else {
                return redirect()->route('sender.index')->with(['message' => 'No hay servidores activos', 'alert-type' => 'error']);
            }
            return 1;
            return redirect()->route('sender.index')->with(['message' => 'Mensaje enviado exitosamente', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            // dd($th);
            return redirect()->route('sender.index')->with(['message' => 'Ocurrió un error en el servidor', 'alert-type' => 'error']);
        }
    }
}
