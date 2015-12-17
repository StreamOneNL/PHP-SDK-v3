<?php

namespace StreamOne\API\v3;

/**
 * Class containing constants for all errors codes the API can report 
 */
class Status
{
	/**
	 * @var int
	 *   Everything went fine
	 */
	 const OK = 0;

	/**
	 * @var int
	 *   Something went wrong internally
	 */
	 const INTERNAL_ERROR = 1;

	/**
	 * @var int
	 *   Provided timestamp is outside of the allowed range
	 */
	 const TIMESTAMP_OUT_OF_RANGE = 2;

	/**
	 * @var int
	 *   Actor authentication failed
	 */
	 const AUTHENTICATION_FAILED = 3;

	/**
	 * @var int
	 *   Access to requested command/action denied for actor
	 */
	 const ACCESS_DENIED = 4;

	/**
	 * @var int
	 *   Supplied command or action was not found
	 */
	 const INVALID_ACTION = 5;

	/**
	 * @var int
	 *   Input for action was not properly specified
	 */
	 const INPUT_ERROR = 6;

	/**
	 * @var int
	 *   One of the supplied parameters was not recognized
	 */
	 const UNKNOWN_PARAMETER = 7;

	/**
	 * @var int
	 *   This request has been rate limited (due to invalid authentication)
	 */
	 const RATE_LIMITED = 8;

	/**
	 * @var int
	 *   Given timezone is not valid
	 */
	 const INVALID_TIMEZONE = 9;

	/**
	 * @var int
	 *   The API is in read-only mode
	 */
	 const API_IN_READONLY_MODE = 10;

	/**
	 * @var int
	 *   Given action is not marked as read-only or read-write (logic error)
	 */
	 const INVALID_ACTION_TYPE = 90;

	/**
	 * @var int
	 *   The requested job was not found
	 */
	 const JOB_NOT_FOUND = 100;

	/**
	 * @var int
	 *   The requested worker was not found
	 */
	 const WORKER_NOT_FOUND = 101;

	/**
	 * @var int
	 *   The given worker does not match the requirements
	 */
	 const WORKER_INVALID = 102;

	/**
	 * @var int
	 *   The job is in an invalid state for this operation
	 */
	 const JOB_INVALID_STATUS = 103;

	/**
	 * @var int
	 *   The given worker does not run on the correct server
	 */
	 const WORKER_WRONG_SERVER = 104;

	/**
	 * @var int
	 *   The requested live task was not found
	 */
	 const LIVE_TASK_NOT_FOUND = 105;

	/**
	 * @var int
	 *   The live task is in an invalid state for this operation
	 */
	 const LIVE_TASK_INVALID_STATUS = 106;

	/**
	 * @var int
	 *   The worker already has this job / live task type
	 */
	 const WORKER_ALREADY_HAS_TYPE = 107;

	/**
	 * @var int
	 *   The worker does not have this job / live task type
	 */
	 const WORKER_DOES_NOT_HAVE_TYPE = 108;

	/**
	 * @var int
	 *   The server already has a worker
	 */
	 const SERVER_ALREADY_HAS_WORKER = 109;

	/**
	 * @var int
	 *   The requested file was not found
	 */
	 const FILE_NOT_FOUND = 110;

	/**
	 * @var int
	 *   The requested file has a different extension
	 */
	 const FILE_WRONG_EXTENSION = 111;

	/**
	 * @var int
	 *   The requested file has a different size
	 */
	 const FILE_WRONG_SIZE = 112;

	/**
	 * @var int
	 *   The requested file location was not found
	 */
	 const FILE_LOCATION_NOT_FOUND = 113;

	/**
	 * @var int
	 *   The requested file server was not found
	 */
	 const FILE_SERVER_NOT_FOUND = 114;

	/**
	 * @var int
	 *   The requested mount was not found
	 */
	 const FILE_MOUNT_NOT_FOUND = 115;

	/**
	 * @var int
	 *   The requested mount does not reside on the requested server
	 */
	 const FILE_MOUNT_NOT_ON_SERVER = 116;

	/**
	 * @var int
	 *   The location to be added already exists
	 */
	 const FILE_LOCATION_EXISTS = 117;

	/**
	 * @var int
	 *   No suitable mount found
	 */
	 const FILE_NO_SUITABLE_MOUNT = 118;

	/**
	 * @var int
	 *   Unable to copy file
	 */
	 const FILE_COPY_FAILED = 119;

	/**
	 * @var int
	 *   The requested item could not be found
	 */
	 const ITEM_NOT_FOUND = 120;

	/**
	 * @var int
	 *   The requested item does not belong to the active account
	 */
	 const ITEM_INVALID_ACCOUNT = 121;

	/**
	 * @var int
	 *   The requsted item file was not found
	 */
	 const ITEM_FILE_NOT_FOUND = 122;

	/**
	 * @var int
	 *   The requested item file does not belong to the requested item
	 */
	 const ITEM_FILE_WRONG_ITEM = 123;

	/**
	 * @var int
	 *   The requested item has no files
	 */
	 const ITEM_NO_FILES = 124;

	/**
	 * @var int
	 *   The category already contains this item
	 */
	 const ITEM_ALREADY_IN_CATEGORY = 125;

	/**
	 * @var int
	 *   The category does not contain this item
	 */
	 const ITEM_NOT_IN_CATEGORY = 126;

	/**
	 * @var int
	 *   The requested server could not be found
	 */
	 const SERVER_NOT_FOUND = 130;

	/**
	 * @var int
	 *   The requested server does not have a required role
	 */
	 const SERVER_MISSING_ROLE = 131;

	/**
	 * @var int
	 *   This actor does not have permission to add this role to this server
	 */
	 const SERVER_NOT_PERMITTED = 132;

	/**
	 * @var int
	 *   This server already has this role
	 */
	 const SERVER_ROLE_ALREADY_ASSIGNED = 133;

	/**
	 * @var int
	 *   The requested server role was not found
	 */
	 const SERVER_ROLE_NOT_FOUND = 134;

	/**
	 * @var int
	 *   This server does not have this role
	 */
	 const SERVER_ROLE_NOT_ASSIGNED = 135;

	/**
	 * @var int
	 *   The requested file category was not found
	 */
	 const FILE_CATEGORY_NOT_FOUND = 140;

	/**
	 * @var int
	 *   The requested file is empty (has no locations)
	 */
	 const FILE_EMPTY = 141;

	/**
	 * @var int
	 *   The requested file is not empty (has locations)
	 */
	 const FILE_NOT_EMPTY = 142;

	/**
	 * @var int
	 *   The requested file has a different MD5 hash
	 */
	 const FILE_WRONG_MD5 = 143;

	/**
	 * @var int
	 *   The requested external mount does not exist
	 */
	 const FILE_EXTERNAL_MOUNT_NOT_FOUND = 144;

	/**
	 * @var int
	 *   The requested file was not found on the external mount
	 */
	 const FILE_NOT_FOUND_ON_EXTERNAL_MOUNT = 145;

	/**
	 * @var int
	 *   The requested event was not found
	 */
	 const EVENT_NOT_FOUND = 150;

	/**
	 * @var int
	 *   The requested event hook was not found
	 */
	 const EVENT_HOOK_NOT_FOUND = 160;

	/**
	 * @var int
	 *   The given target is invalid
	 */
	 const EVENT_HOOK_INVALID_TARGET = 161;

	/**
	 * @var int
	 *   The requested event hook type was not found
	 */
	 const EVENT_HOOK_TYPE_NOT_FOUND = 170;

	/**
	 * @var int
	 *   The event hook type is invalid for this operation
	 */
	 const EVENT_HOOK_TYPE_INVALID = 171;

	/**
	 * @var int
	 *   The requested event hook log entry was not found
	 */
	 const EVENT_HOOK_LOG_NOT_FOUND = 180;

	/**
	 * @var int
	 *   The requested event hook log entry is of the wrong type
	 */
	 const EVENT_HOOK_LOG_WRONG_TYPE = 181;

	/**
	 * @var int
	 *   The requested event hook log entry could not be handled
	 */
	 const EVENT_HOOK_LOG_CANNOT_HANDLE = 182;

	/**
	 * @var int
	 *   The requested upload token was not found
	 */
	 const UPLOAD_TOKEN_NOT_FOUND = 190;

	/**
	 * @var int
	 *   The requested transcode profile was not found
	 */
	 const TRANSCODE_PROFILE_NOT_FOUND = 200;

	/**
	 * @var int
	 *   The transcode source does not have a video track
	 */
	 const TRANSCODE_SOURCE_HAS_NO_VIDEO = 201;

	/**
	 * @var int
	 *   The transcode source does not have an audio track
	 */
	 const TRANSCODE_SOURCE_HAS_NO_AUDIO = 202;

	/**
	 * @var int
	 *   The requested category was not found
	 */
	 const CATEGORY_NOT_FOUND = 210;

	/**
	 * @var int
	 *   The given parent has the provided category as parent, would create a loop
	 */
	 const CATEGORY_PARENT_WRONG = 211;

	/**
	 * @var int
	 *   The category still has items linked to it
	 */
	 const CATEGORY_HAS_LINKED_ITEMS = 212;

	/**
	 * @var int
	 *   The requested playlist was not found
	 */
	 const PLAYLIST_NOT_FOUND = 220;

	/**
	 * @var int
	 *   The requested playlist entry was not found
	 */
	 const PLAYLIST_ENTRY_NOT_FOUND = 230;

	/**
	 * @var int
	 *   The requested livestream was not found
	 */
	 const LIVESTREAM_NOT_FOUND = 240;

	/**
	 * @var int
	 *   The livestream is already started
	 */
	 const LIVESTREAM_ALREADY_STARTED = 241;

	/**
	 * @var int
	 *   The livestream is not yet started
	 */
	 const LIVESTREAM_NOT_YET_STARTED = 242;

	/**
	 * @var int
	 *   The requested livestream type was not found
	 */
	 const LIVESTREAM_TYPE_NOT_FOUND = 243;

	/**
	 * @var int
	 *   The requested customer was not found
	 */
	 const CUSTOMER_NOT_FOUND = 250;

	/**
	 * @var int
	 *   The requested application was not found
	 */
	 const APPLICATION_NOT_FOUND = 260;

	/**
	 * @var int
	 *   The requested role can not be added or removed by the current actor
	 */
	 const APPLICATION_NOT_PERMITTED = 261;

	/**
	 * @var int
	 *   The requested role is already assigned to this application
	 */
	 const APPLICATION_ROLE_ALREADY_ASSIGNED = 262;

	/**
	 * @var int
	 *   The requested role is not yet assigned to this application
	 */
	 const APPLICATION_ROLE_NOT_ASSIGNED = 263;

	/**
	 * @var int
	 *   The requested role was not found
	 */
	 const ROLE_NOT_FOUND = 270;

	/**
	 * @var int
	 *   The requested token was not found
	 */
	 const TOKEN_NOT_FOUND = 271;

	/**
	 * @var int
	 *   The role already has this token
	 */
	 const ROLE_ALREADY_HAS_TOKEN = 272;

	/**
	 * @var int
	 *   The requested role does not have this token
	 */
	 const ROLE_DOES_NOT_HAVE_TOKEN = 273;

	/**
	 * @var int
	 *   The requested token can not be added or removed by the current actor
	 */
	 const ROLE_NOT_PERMITTED = 274;

	/**
	 * @var int
	 *   This role is still used for an application or user
	 */
	 const ROLE_STILL_USED = 275;

	/**
	 * @var int
	 *   Creating the session required an API version 2 hash, but it was not supplied
	 */
	 const SESSION_NEEDS_V2_HASH = 280;

	/**
	 * @var int
	 *   The session could not be created due to some reason
	 */
	 const SESSION_INVALID = 281;

	/**
	 * @var int
	 *   The requested session was not found
	 */
	 const SESSION_NOT_FOUND = 282;

	/**
	 * @var int
	 *   The requested account was not found
	 */
	 const ACCOUNT_NOT_FOUND = 290;

	/**
	 * @var int
	 *   The account already has this profile group
	 */
	 const ACCOUNT_ALREADY_HAS_PROFILE_GROUP = 291;

	/**
	 * @var int
	 *   The account does not have this profile group
	 */
	 const ACCOUNT_DOES_NOT_HAVE_PROFILE_GROUP = 292;

	/**
	 * @var int
	 *   The requested profile group was not found
	 */
	 const PROFILE_GROUP_NOT_FOUND = 300;

	/**
	 * @var int
	 *   The requestes user was not found
	 */
	 const USER_NOT_FOUND = 310;

	/**
	 * @var int
	 *   The requested role can not be added or removed by the current actor
	 */
	 const USER_NOT_PERMITTED = 311;

	/**
	 * @var int
	 *   The requested role is already assigned to this user
	 */
	 const USER_ROLE_ALREADY_ASSIGNED = 312;

	/**
	 * @var int
	 *   The requested role is not yet assigned to this user
	 */
	 const USER_ROLE_NOT_ASSIGNED = 313;

	/**
	 * @var int
	 *   The user password change request is invalid
	 */
	 const USER_PASSWORD_CHANGE_INVALID = 314;

	/**
	 * @var int
	 *   The user password reset request was not found or is expired
	 */
	 const USER_PASSWORD_RESET_NOT_FOUND_OR_EXPIRED = 315;

	/**
	 * @var int
	 *   The username already exists
	 */
	 const USER_USERNAME_ALREADY_EXISTS = 316;

	/**
	 * @var int
	 *   The requested schedule was not found
	 */
	 const SCHEDULE_NOT_FOUND = 320;

	/**
	 * @var int
	 *   The schedule already has this category
	 */
	 const SCHEDULE_ALREADY_HAS_CATEGRY = 325;

	/**
	 * @var int
	 *   The schedule does not have this category
	 */
	 const SCHEDULE_DOES_NOT_HAVE_CATEGRY = 326;

	/**
	 * @var int
	 *   The requested player was not found
	 */
	 const PLAYER_NOT_FOUND = 330;

	/**
	 * @var int
	 *   The player already has this origin
	 */
	 const PLAYER_ALREADY_HAS_ORIGIN = 331;

	/**
	 * @var int
	 *   The player does not have this origin
	 */
	 const PLAYER_DOES_NOT_HAVE_ORIGIN = 332;

	/**
	 * @var int
	 *   The requested origin was not found
	 */
	 const ORIGIN_NOT_FOUND = 340;

	/**
	 * @var int
	 *   The requested platform support task was not found
	 */
	 const PLATFORM_SUPPORT_TASK_NOT_FOUND = 350;

	/**
	 * @var int
	 *   The requested job type was not found
	 */
	 const JOB_TYPE_NOT_FOUND = 360;

	/**
	 * @var int
	 *   The requested live task type was not found
	 */
	 const LIVE_TASK_TYPE_NOT_FOUND = 361;

	/**
	 * @var int
	 *   The requested profile was not found
	 */
	 const PROFILE_NOT_FOUND = 370;

	/**
	 * @var int
	 *   The requested item file format was not found
	 */
	 const ITEM_FILE_FORMAT_NOT_FOUND = 371;

	/**
	 * @var int
	 *   The requested item type was not found
	 */
	 const ITEM_TYPE_NOT_FOUND = 372;

	/**
	 * @var int
	 *   The requested audio codec was not found
	 */
	 const AUDIO_CODEC_NOT_FOUND = 373;

	/**
	 * @var int
	 *   The requested video codec was not found
	 */
	 const VIDEO_CODEC_NOT_FOUND = 374;

	/**
	 * @var int
	 *   The requested video codec profile was not found
	 */
	 const VIDEO_CODEC_PROFILE_NOT_FOUND = 375;

	/**
	 * @var int
	 *   The requested profile belongs to a specific account, but the profile group is system-wide
	 */
	 const PROFILE_IS_OF_SPECIFIC_ACCOUNT = 376;

	/**
	 * @var int
	 *   The profile group already has this profile
	 */
	 const PROFILE_GROUP_ALREADY_HAS_PROFILE = 377;

	/**
	 * @var int
	 *   The profile group does not have this profile
	 */
	 const PROFILE_GROUP_DOES_NOT_HAVE_PROFILE = 378;

	/**
	 * @var int
	 *   The profile group has an height that is not supported for interlaced output
	 */
	 const PROFILE_INVALID_HEIGHT_FOR_INTERLACED = 379;

	/**
	 * @var int
	 *   The requested security profile was not found
	 */
	 const SECURITY_PROFILE_NOT_FOUND = 380;

	/**
	 * @var int
	 *   The security rule is not valid
	 */
	 const SECURITY_RULE_NOT_VALID = 381;

	/**
	 * @var int
	 *   The requested security rule was not found
	 */
	 const SECURITY_RULE_NOT_FOUND = 382;

	/**
	 * @var int
	 *   An invalid time selection range was supplied (spans zero or negative time)
	 */
	 const STATS_RANGE_INVALID = 390;

	/**
	 * @var int
	 *   An invalid resoltuion was supplied
	 */
	 const STATS_RESOLUTION_INVALID = 391;

	/**
	 * @var int
	 *   An invalid scope was supplied
	 */
	 const STATS_SCOPE_INVALID = 392;

	/**
	 * @var int
	 *   The requested client group was not found
	 */
	 const CLIENT_GROUP_NOT_FOUND = 400;

	/**
	 * @var int
	 *   The requested client was not found
	 */
	 const CLIENT_NOT_FOUND = 401;

	/**
	 * @var int
	 *   The client group already contains this client
	 */
	 const CLIENT_GROUP_ALREADY_HAS_CLIENT = 402;

	/**
	 * @var int
	 *   The client group does not contain this client
	 */
	 const CLIENT_GROUP_DOES_NOT_HAVE_CLIENT = 403;

	/**
	 * @var int
	 *   The requested record task could not be found
	 */
	 const RECORD_TASK_NOT_FOUND = 410;

	/**
	 * @var int
	 *   The record task has an invalid status
	 */
	 const RECORD_TASK_INVALID_STATUS = 411;

	/**
	 * @var int
	 *   The FTP user with the given username already exists
	 */
	 const FTP_USER_ALREADY_EXISTS = 420;

	/**
	 * @var int
	 *   The FTP user could not be found
	 */
	 const FTP_USER_NOT_FOUND = 421;

	/**
	 * @var int
	 *   The requested Single Sign-On service could not be found
	 */
	 const SSO_SERVICE_NOT_FOUND = 430;

	/**
	 * @var int
	 *   The given user already has a token for the given Single Sign-On service
	 */
	 const USER_ALREADY_HAS_SSO_TOKEN = 431;

	/**
	 * @var int
	 *   The given user has no token for the given Single Sign-On service
	 */
	 const USER_HAS_NO_SSO_TOKEN = 432;

	/**
	 * @var int
	 *   The given SSO response is invalid
	 */
	 const SSO_RESPONSE_INVALID = 433;

	/**
	 * @var int
	 *   The given SSO token is unknown
	 */
	 const SSO_TOKEN_UNKNOWN = 434;

	/**
	 * @var int
	 *   The requested item has no poster archive
	 */
	 const ITEM_NO_POSTER_ARCHIVE = 440;

	/**
	 * @var int
	 *   The specified poster index is not present in the poster archive
	 */
	 const ITEM_POSTER_INDEX_NOT_FOUND = 441;

}
