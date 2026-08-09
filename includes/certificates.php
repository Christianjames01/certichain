<?php
declare(strict_types=1);

/**
 * includes/certificates.php
 *
 * Catalog data + helpers for the registrar's certificate/document services.
 * Required by certservices.php (and referenced indirectly by index.php).
 *
 * Exposes:
 *   $CERT_CATEGORIES   - associative array of category => details/items
 *   certSlug(string)   - turns a certificate title into a URL-safe slug
 *   certFind(array, slug) - looks up a certificate by slug, returns full detail array
 */

// ---------------------------------------------------------------------
// Slug helper
// ---------------------------------------------------------------------
if (!function_exists('certSlug')) {
    function certSlug(string $title): string
    {
        $slug = strtolower($title);
        $slug = str_replace('&', 'and', $slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        return trim($slug, '-');
    }
}

// ---------------------------------------------------------------------
// Category defaults
// Shared fee / processing / requirements / steps used unless a specific
// certificate overrides them below.
// ---------------------------------------------------------------------
$CERT_DEFAULTS = [
    'enrollment' => [
        'fee'          => '₱50.00',
        'processing'   => '1–2 working days',
        'requirements' => [
            'Valid school ID or government-issued ID',
            'Duly accomplished request form (available in the student portal)',
            'Proof of payment of applicable fees',
        ],
        'steps' => [
            'Log in to your CertiChain AI student portal.',
            'Select this certificate under "Request a Document."',
            'Fill out and submit the request form.',
            'Pay the fee at the Finance Office and upload your Official Receipt.',
            'Wait for registrar approval, then claim your certificate at the Registrar\'s Office.',
        ],
    ],
    'academic-records' => [
        'fee'          => '₱150.00 per copy',
        'processing'   => '3–5 working days',
        'requirements' => [
            'Valid school ID or government-issued ID',
            'Cleared academic and financial records',
            'Duly accomplished request form',
            'Authorization letter and ID of representative, if claiming on your behalf',
        ],
       'steps' => [
            'Log in to your CertiChain AI student portal.',
            'Select this document under "Academic Records."',
            'Indicate the purpose and number of copies needed.',
            'Pay the fee at the Finance Office and upload your Official Receipt.',
            'The registrar reviews and prepares the document, then notifies you when it is ready for release.',
        ],
    ],
    'graduation' => [
        'fee'          => '₱300.00',
        'processing'   => '5–10 working days (subject to Board approval schedule)',
        'requirements' => [
            'Completed graduation application form',
            'Cleared academic, financial, and library records',
            'Latest Certificate of Grades or unofficial TOR',
            'Two recent 2x2 ID photos (white background)',
        ],
        'steps' => [
            'Submit your graduation application through the student portal during the announced filing period.',
            'Complete your online clearance across all departments.',
            'Wait for the registrar and program department to verify completion of requirements.',
            'Once endorsed by the Board, your certificate becomes available for download or claiming.',
        ],
    ],
    'diploma' => [
        'fee'          => '₱500.00',
        'processing'   => '10–15 working days',
        'requirements' => [
            'Proof of graduation (Certificate of Graduation or endorsed TOR)',
            'Cleared financial records',
            'Valid government-issued ID',
            'Police/affidavit of loss, if requesting a replacement',
        ],
        'steps' => [
            'Log in to your student/alumni portal and select this document.',
            'Upload supporting documents if requesting a replacement or duplicate.',
            'Pay the fee at the Finance Office and upload your Official Receipt.',
            'The registrar prepares and reviews the diploma record.',
            'Claim your diploma in person at the Registrar\'s Office once notified.',
        ],
    ],
    'transfer' => [
        'fee'          => '₱100.00',
        'processing'   => '3–7 working days',
        'requirements' => [
            'Cleared financial and library records',
            'Letter of intent to transfer or withdraw',
            'Valid school ID or government-issued ID',
        ],
        'steps' => [
            'Submit a request through the student portal, stating your reason for transfer/withdrawal.',
            'Complete clearance routing across all departments.',
            'Settle any outstanding balance.',
            'The registrar processes and releases your document once clearance is confirmed.',
        ],
    ],
    'authentication' => [
        'fee'          => '₱120.00',
        'processing'   => '2–4 working days',
        'requirements' => [
            'The original document to be authenticated or verified',
            'Valid government-issued ID',
            'Requesting party details (employer, agency, or institution), if applicable',
        ],
       'steps' => [
            'Upload or present the document you need authenticated.',
            'The registrar cross-checks it against official academic records.',
            'Pay the fee at the Finance Office and upload your Official Receipt.',
            'Claim the authenticated copy in person at the Registrar\'s Office.',
        ],
    ],
    'curriculum' => [
        'fee'          => '₱80.00',
        'processing'   => '2–5 working days',
        'requirements' => [
            'Valid school ID or government-issued ID',
            'Program and year/curriculum you are requesting information for',
            'Duly accomplished request form',
        ],
       'steps' => [
            'Log in to the student portal and select this certificate.',
            'Specify the program, curriculum year, and purpose.',
            'Pay the fee at the Finance Office and upload your Official Receipt.',
            'Claim the certificate at the Registrar\'s Office once the request is approved.',
        ],
    ],
    'special-purpose' => [
        'fee'          => '₱100.00',
        'processing'   => '2–5 working days',
        'requirements' => [
            'Valid school ID or government-issued ID',
            'Name and address of the receiving institution/agency',
            'Duly accomplished request form',
        ],
       'steps' => [
            'Log in to the student portal and select this certificate.',
            'Indicate the specific purpose and the receiving party.',
            'Pay the fee at the Finance Office and upload your Official Receipt.',
            'Claim the certificate at the Registrar\'s Office once approved, or have it sent directly to the receiving institution.',
        ],
    ],
    'clearance' => [
        'fee'          => 'Varies by balance/department',
        'processing'   => 'Same day to 3 working days, depending on department response',
        'requirements' => [
            'Valid school ID or government-issued ID',
            'No pending accountabilities (books, equipment, org dues, etc.)',
        ],
        'steps' => [
            'Log in to the student portal and open "Clearance."',
            'The system routes your clearance to each department automatically.',
            'Track sign-offs in real time and settle any flagged balances at the Finance Office.',
            'Once all departments clear you, your clearance certificate is issued automatically.',
        ],
    ],
    'printouts' => [
        'fee'          => '₱20.00',
        'processing'   => 'Same day to 1 working day',
        'requirements' => [
            'Valid school ID or government-issued ID',
            'Duly accomplished request form',
        ],
        'steps' => [
            'Log in to the student portal and select this document under "Printouts."',
            'Confirm the term/school year the printout should cover.',
            'Pay the fee at the Finance Office and upload your Official Receipt.',
            'Claim your printed copy at the Registrar\'s Office once released.',
        ],
    ],
    'maritime' => [
        'fee'          => '₱150.00',
        'processing'   => '5–10 working days (subject to CHED/MARINA processing schedules)',
        'requirements' => [
            'Valid school ID or government-issued ID',
            'BSMT program clearance from the Program Head/OBTO',
            'Duly accomplished request form',
            'Recent scanned ID picture, if required for the specific document',
        ],
        'steps' => [
            'Log in to the student portal and select this document under "Maritime (BSMT) Documents."',
            'Indicate the purpose (e.g. board exam application, employment, cross-enrollment).',
            'Route the request for Program Head/OBTO clearance.',
            'Pay the fee at the Finance Office and upload your Official Receipt.',
            'Claim your document at the Registrar\'s Office once approved and released.',
        ],
    ],
];

// ---------------------------------------------------------------------
// Category catalog
// icon = inner SVG markup only (paths/shapes), matching index.php icons
// items = Title => short summary shown on cards/tiles
// ---------------------------------------------------------------------
$CERT_CATEGORIES = [
    'enrollment' => [
        'label' => 'Enrollment & Student Status',
        'icon'  => '<path d="M22 9L12 5 2 9l10 4 10-4z" /><path d="M6 10.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-5.5" /><path d="M22 9v6" />',
        'items' => [
            'Certificate of Enrollment (COE)'                  => 'Confirms that you are currently enrolled for the current term.',
            'Certificate of Enrollment w/ Units Earned'        => 'Confirms enrollment together with the total units you have earned to date.',
            'Certificate of Enrollment w/ Subjects Enrolled'   => 'Confirms enrollment together with the specific subjects you are currently taking.',
            'Certificate of Registration (COR)'                => 'Official proof of your registered subjects and units for the term.',
            'Certificate of Student Status'                    => 'Confirms your current standing as an active student.',
            'Certificate of Current Enrollment'                => 'Verifies enrollment status as of the current academic period.',
            'Certificate of Attendance'                        => 'Confirms attendance in a program, seminar, or academic period.',
            'Certificate of Academic Load'                     => 'States the total units/subjects you are officially carrying this term.',
            'Certificate of Residency'                         => 'Confirms continuous residency/enrollment in the institution.',
            'Certificate of Active Student Status'             => 'Confirms you are an active, currently enrolled student in good standing.',
            'Certificate of Irregular/Regular Status'          => 'States whether you are classified as a regular or irregular student.',
            'Certificate of Remaining Units/Subjects'          => 'Lists the units or subjects you still need to complete your program.',
            'Certificate of Cross-Enroll Permit'               => 'Authorizes you to take specific subjects at another institution.',
        ],
    ],
    'academic-records' => [
        'label' => 'Academic Records',
        'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><path d="M14 2v6h6" /><path d="M9 13h6M9 17h6M9 9h1" />',
        'items' => [
            'Official Transcript of Records (TOR)'                    => 'Complete, official record of all your grades and subjects taken.',
            'Certified True Copy of Transcript of Records'            => 'A certified duplicate of your official TOR.',
            'Reference Copy of Transcript of Records'                 => 'A non-transferable copy of your transcript issued strictly for reference purposes.',
            'Transcript of Records for Employment Purposes'          => 'TOR issued specifically for job application requirements.',
            'Transcript of Records for Board Examination Purposes'   => 'TOR formatted for submission to the PRC or licensure boards.',
            'Transcript of Records for Foreign Evaluation'           => 'TOR prepared for credential evaluation abroad.',
            'Certification of Grades'                                 => 'Confirms grades earned in specific subjects or terms.',
            'Certification of General Weighted Average (GWA)'        => 'States your computed GWA as of the latest term.',
            'Certification of Academic Records'                       => 'General certification summarizing your academic record.',
            'Certification of Units Earned'                           => 'States the total number of units you have completed.',
            'Certification of Subjects Taken'                         => 'Lists the specific subjects you have completed.',
            'Certification of Completion of Academic Requirements'   => 'Confirms you have completed all academic requirements for your program.',
            'Certificate of Completed Academic Requirements (CAR)'   => 'Confirms completion of all academic requirements, with or without the comprehensive exam.',
            'Certificate of Grade for Cross-Enrollee'                 => 'Certifies the grade earned in a subject taken as a cross-enrollee.',
            'Letter of Confirmation'                                  => 'Confirms specific academic details on request, in letter form.',
            'Letter of No Objection'                                  => 'States the institution has no objection to a specific request or arrangement.',
        ],
    ],
    'graduation' => [
        'label' => 'Graduation',
        'icon'  => '<circle cx="12" cy="9" r="6" /><path d="M9.5 8.5l1.8 1.8L15 6.5" /><path d="M8 14.5L6 22l6-3 6 3-2-7.5" />',
        'items' => [
            'Certificate of Graduation'               => 'Official confirmation that you have graduated from your program.',
            'Certificate of Graduation Completion'    => 'Confirms completion of all graduation requirements.',
            'Certificate of Candidacy for Graduation' => 'Confirms you are an official candidate for the upcoming graduation.',
            'Certificate of Degree Completion'        => 'Confirms completion of all requirements for your degree.',
            'Certificate of Academic Completion'      => 'Confirms full academic completion of your program of study.',
            'Certificate of Honors / Awards'          => 'Documents academic honors or awards received upon graduation.',
        ],
    ],
    'diploma' => [
        'label' => 'Diploma',
        'icon'  => '<circle cx="12" cy="8" r="5" /><path d="M8.5 12.5L7 22l5-3 5 3-1.5-9.5" />',
        'items' => [
            'Original Diploma'                   => 'Your official diploma issued upon graduation.',
            'Certified True Copy of Diploma'     => 'A certified duplicate copy of your original diploma.',
            'Replacement / Duplicate Diploma'    => 'Reissuance of a lost, damaged, or destroyed diploma.',
            'Diploma Authentication Certificate' => 'Confirms the authenticity of a diploma issued by the institution.',
        ],
    ],
    'transfer' => [
        'label' => 'Transfer & Withdrawal',
        'icon'  => '<path d="M9 3H5a2 2 0 0 0-2 2v4m18 0V5a2 2 0 0 0-2-2h-4m0 18h4a2 2 0 0 0 2-2v-4M3 15v4a2 2 0 0 0 2 2h4" />',
        'items' => [
            'Certificate of Transfer Credential'      => 'Official credential needed to transfer to another institution.',
            'Certificate of Honorable Dismissal'      => 'Confirms you left the institution in good standing.',
            'Transfer Credential'                     => 'Summarizes academic standing for transfer purposes.',
            'Certificate of Withdrawal'                => 'Confirms official withdrawal from a program or term.',
            'Certificate of No Objection for Transfer' => 'States the institution has no objection to your transfer.',
            'Certificate of No Record'                 => 'Confirms no enrollment record exists for a given period.',
        ],
    ],
    'authentication' => [
        'label' => 'Authentication & Verification',
        'icon'  => '<rect x="3" y="3" width="7" height="7" /><rect x="14" y="3" width="7" height="7" /><rect x="3" y="14" width="7" height="7" /><path d="M14 14h3v3h-3zM19 14h2M14 19h2M19 19h2" />',
        'items' => [
            'Certification, Authentication & Verification (CAV)' => 'Formal CAV confirming the authenticity of your academic documents.',
            'Academic Credential Verification Certificate'       => 'Confirms the validity of an academic credential you hold.',
            'School Record Verification Certificate'             => 'Verifies details in your school records upon request.',
            'Document Authentication Certificate'                => 'Authenticates a specific registrar-issued document.',
            'Certificate of Authenticity of Academic Records'    => 'Confirms your academic records are genuine and unaltered.',
            'Certified True Copy / Authentication (Any Document)' => 'Certifies a true copy of any registrar-issued document upon request.',
            'Scanning of Documents'                              => 'Digitizes a physical registrar document into a scanned copy for submission.',
        ],
    ],
    'curriculum' => [
        'label' => 'Curriculum & Course',
        'icon'  => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" /><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />',
        'items' => [
            'Certificate of Curriculum'              => 'Describes the curriculum you were enrolled under.',
            'Course Description Certificate'         => 'Provides official descriptions of specific courses taken.',
            'Certification of Course Syllabus'       => 'Confirms the syllabus/content of a course you completed.',
            'Certification of Subjects & Units'      => 'Lists subjects and corresponding units under your curriculum.',
            'Certification of Medium of Instruction' => 'Confirms the language of instruction used in your program.',
            'Certification of Program Completion'    => 'Confirms completion of a specific academic program.',
        ],
    ],
    'special-purpose' => [
        'label' => 'Special Purpose',
        'icon'  => '<rect x="3" y="3" width="18" height="18" rx="2" /><path d="M8 12l3 3 5-6" />',
        'items' => [
            'Certificate for Employment Requirement'                => 'Prepared specifically to support a job application.',
            'Certificate for Scholarship Requirement'               => 'Prepared specifically to support a scholarship application.',
            'Certificate for Internship/OJT Requirement'            => 'Prepared specifically to support internship/OJT placement.',
            'Certificate for Visa Requirement'                      => 'Prepared specifically to support a visa application.',
            'Certificate for Embassy Requirement'                   => 'Prepared specifically for embassy submission requirements.',
            'Certificate for Graduate School Admission'             => 'Prepared specifically to support graduate school applications.',
            'Certificate for Professional Examination Requirement'  => 'Prepared specifically to support licensure/board exam applications.',
            'Abu Dhabi Certificate'                                 => 'Certification formatted per Abu Dhabi authorities\' requirements for overseas use.',
            'Qatar Certificate'                                     => 'Certification formatted per Qatar authorities\' requirements for overseas use.',
        ],
    ],
    'clearance' => [
        'label' => 'Clearance & Payments',
        'icon'  => '<rect x="3" y="3" width="18" height="18" rx="2" /><path d="M8 12l3 3 5-6" />',
        'items' => [
            'Online Clearance Routing Across Departments' => 'Route your clearance form to every department online and track sign-offs.',
            'Settle Outstanding Balances Online'          => 'Pay any outstanding fees or balances directly through the portal.',
        ],
    ],
    'printouts' => [
        'label' => 'Printouts & Simple Requests',
        'icon'  => '<path d="M6 9V2h12v7" /><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" /><rect x="6" y="14" width="12" height="8" />',
        'items' => [
            'Print-out of Evaluation'            => 'A printed copy of your subject evaluation/curriculum checklist.',
            'Print-out of Class Schedule'        => 'A printed copy of your official class schedule for the term.',
            'Print-out of Grades (Report Card)'  => 'A printed copy of your grades for the term.',
        ],
    ],
    'maritime' => [
        'label' => 'Maritime (BSMT) Program Documents',
        'icon'  => '<path d="M3 17l1.5-5h15L21 17" /><path d="M5 12V6l7-3 7 3v6" /><path d="M2 20c2 1.5 4 1.5 6 0s4-1.5 6 0 4 1.5 6 0" />',
        'items' => [
            'CHED Memo Order (BSMT)'                              => 'Official CHED memorandum order document required for BSMT students.',
            'Certificate of Completion (BSMT)'                    => 'Confirms completion of the BSMT program requirements.',
            'Certificate of Registration/Competency (COR/COC – BSMT)' => 'Registration/competency certificate required for the maritime licensure board exam.',
            'Board Exam Certification w/ Scanned Picture'         => 'Certification with an attached scanned photo, required for board exam application.',
            'TESDA Certificate'                                   => 'Certification of TESDA-related training or qualification.',
            'Special Order (S.O.)'                                => 'Confirms the Special Order number issued by CHED for your program/batch.',
        ],
    ],
];

// ---------------------------------------------------------------------
// Optional per-certificate overrides.
// Only add entries here for certificates that need details different
// from their category defaults above. Keyed by exact title.
// ---------------------------------------------------------------------
$CERT_OVERRIDES = [
    'Official Transcript of Records (TOR)' => [
        'fee'        => '₱150.00 per copy (₱250.00 for rush requests)',
        'processing' => '5–7 working days (2–3 working days for rush requests)',
    ],
    'Original Diploma' => [
        'processing' => '15–20 working days (issued only after Board confirmation of graduation)',
    ],
];

// ---------------------------------------------------------------------
// certFind: look up a single certificate by slug and return its full
// detail record, merging category defaults with any overrides.
// ---------------------------------------------------------------------
if (!function_exists('certFind')) {
    function certFind(array $categories, string $slug): ?array
    {
        global $CERT_DEFAULTS, $CERT_OVERRIDES;

        foreach ($categories as $catKey => $cat) {
            foreach ($cat['items'] as $title => $summary) {
                if (certSlug($title) !== $slug) {
                    continue;
                }

                $defaults = $CERT_DEFAULTS[$catKey] ?? [
                    'fee'          => 'Contact the registrar',
                    'processing'   => '3–5 working days',
                    'requirements' => ['Valid school ID or government-issued ID', 'Duly accomplished request form'],
                    'steps'        => ['Log in to the student portal.', 'Submit your request.', 'Pay the fee at the Finance Office and upload your Official Receipt.', 'Wait for approval, then claim your document at the Registrar\'s Office.'],
                ];
                $override = $CERT_OVERRIDES[$title] ?? [];

                return [
                    'title'          => $title,
                    'summary'        => $summary,
                    'category_key'   => $catKey,
                    'category_label' => $cat['label'],
                    'fee'            => $override['fee'] ?? $defaults['fee'],
                    'processing'     => $override['processing'] ?? $defaults['processing'],
                    'requirements'   => $override['requirements'] ?? $defaults['requirements'],
                    'steps'          => $override['steps'] ?? $defaults['steps'],
                ];
            }
        }

        return null;
    }
}