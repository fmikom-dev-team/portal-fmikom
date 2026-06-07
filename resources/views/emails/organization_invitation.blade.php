<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation to join {{ $organizationName }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f6f8; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; width: 100%;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); border-radius: 12px 12px 0 0; padding: 36px 40px 32px; text-align: center;">
                            <!-- Logo / Brand -->
                            <div style="display: inline-flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                                <div style="width: 36px; height: 36px; background: rgba(255,255,255,0.2); border-radius: 8px; display: inline-flex; align-items: center; justify-content: center;">
                                    <span style="color: white; font-size: 18px; font-weight: 800;">W</span>
                                </div>
                                <span style="color: white; font-size: 18px; font-weight: 700; letter-spacing: -0.3px;">WorkOS · Portal FMIKOM</span>
                            </div>
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 700; letter-spacing: -0.5px; line-height: 1.3;">
                                You've been invited to join
                            </h1>
                            <p style="margin: 8px 0 0; color: rgba(255,255,255,0.85); font-size: 20px; font-weight: 600;">
                                {{ $organizationName }}
                            </p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="background-color: #ffffff; padding: 36px 40px; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb;">
                            
                            <!-- Invite Detail -->
                            <p style="margin: 0 0 24px; font-size: 15px; color: #374151; line-height: 1.6;">
                                Hello! <strong>{{ $invitedBy }}</strong> has invited you to join 
                                <strong>{{ $organizationName }}</strong> on Portal FMIKOM as a 
                                <strong style="color: #2563eb;">{{ $role }}</strong>.
                            </p>

                            <!-- Info Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f3ff; border: 1px solid #ede9fe; border-radius: 10px; margin-bottom: 28px;">
                                <tr>
                                    <td style="padding: 20px 24px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="50%" style="padding-bottom: 12px;">
                                                    <p style="margin: 0; font-size: 11px; font-weight: 600; color: #7c3aed; text-transform: uppercase; letter-spacing: 0.5px;">Organization</p>
                                                    <p style="margin: 4px 0 0; font-size: 14px; font-weight: 600; color: #1f2937;">{{ $organizationName }}</p>
                                                </td>
                                                <td width="50%" style="padding-bottom: 12px;">
                                                    <p style="margin: 0; font-size: 11px; font-weight: 600; color: #7c3aed; text-transform: uppercase; letter-spacing: 0.5px;">Your Role</p>
                                                    <p style="margin: 4px 0 0; font-size: 14px; font-weight: 600; color: #1f2937;">{{ $role }}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%">
                                                    <p style="margin: 0; font-size: 11px; font-weight: 600; color: #7c3aed; text-transform: uppercase; letter-spacing: 0.5px;">Invited By</p>
                                                    <p style="margin: 4px 0 0; font-size: 14px; font-weight: 600; color: #1f2937;">{{ $invitedBy }}</p>
                                                </td>
                                                <td width="50%">
                                                    <p style="margin: 0; font-size: 11px; font-weight: 600; color: #7c3aed; text-transform: uppercase; letter-spacing: 0.5px;">Expires</p>
                                                    <p style="margin: 4px 0 0; font-size: 14px; font-weight: 600; color: #1f2937;">{{ $expiresAt }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA Button -->
                            <div style="text-align: center; margin-bottom: 28px;">
                                <a href="{{ $acceptUrl }}" 
                                   style="display: inline-block; background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); color: #ffffff; font-size: 15px; font-weight: 600; padding: 14px 36px; border-radius: 8px; text-decoration: none; letter-spacing: -0.2px; box-shadow: 0 4px 14px rgba(82,68,228,0.4);">
                                    Accept Invitation →
                                </a>
                            </div>
                            
                            <p style="margin: 0 0 8px; font-size: 13px; color: #6b7280; text-align: center;">
                                Or copy and paste this link into your browser:
                            </p>
                            <p style="margin: 0 0 24px; font-size: 12px; color: #2563eb; text-align: center; word-break: break-all;">
                                {{ $acceptUrl }}
                            </p>

                            <!-- Warning -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; margin-bottom: 8px;">
                                <tr>
                                    <td style="padding: 14px 18px;">
                                        <p style="margin: 0; font-size: 13px; color: #92400e;">
                                            ⚠️ This invitation link will expire on <strong>{{ $expiresAt }}</strong>. 
                                            If you did not expect this invitation, you can safely ignore this email.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-top: 0; border-radius: 0 0 12px 12px; padding: 24px 40px; text-align: center;">
                            <p style="margin: 0 0 6px; font-size: 12px; color: #9ca3af;">
                                This email was sent by Portal FMIKOM WorkOS System
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                © {{ date('Y') }} Portal FMIKOM. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
