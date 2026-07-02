<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Docusign;
use Illuminate\Http\Request;

class DocusignController extends Controller
{
    /**
     * Connect your application to docusign
     *
     * @return url
     */
    public function connect(Project $project, Docusign $docusign)
    {
        try {
            $docusign->setProject($project);
    		return $docusign->connect(route('project.docusign.callback', $project));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occured');
        }
    }

    /**
     * This function called when you auth your application with docusign
     *
     * @return url
     */
    public function callback(Request $request, Project $project, Docusign $docusign)
    {
        $docusign->setProject($project);
        $docusign->storeToken($request->code);

        return redirect()->route('project.prospect.index', $project)->with('success', 'Docusign succesfully connected');
    }
}
