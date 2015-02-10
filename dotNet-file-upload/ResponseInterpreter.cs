using System;
using System.Web.Script.Serialization;
using System.Collections.Generic;
using System.Text;

namespace DataGovAuUpload {
   sealed class ResponseInterpreter {
      private ResponseInterpreter() {
      }
      /*---------------------------------------------------------------------------------------
       * Response from data.gov.au is a JSON string. If successful, it will look something like:
         {
          "help": "...",
          "success"": true, 
          "result": {
              "resource_group_id" : "...",
              "cache_last_updated" : null,
              "datastore_active": false,
              ... 
           }
          }
       * If unsuccessful, the response will look something like:
       *          {
          "help": "...",
          "success"": false, 
          "error": {
              ""__type" : "Validation Error"
           }
          }
       *---------------------------------------------------------------------------------------*/
      public static DataGovOutcome InterpretResponse(string postStatus) {

         Dictionary<string, Object> ResponseFields = Deserialise(postStatus);
         DataGovOutcome Outcome = new DataGovOutcome();
         Outcome.PostStatus = postStatus;
         string OutcomeStatusMessage = ExtractOutcome(ResponseFields);
         string OutcomeStatusBody = ExtractResponseValues(ResponseFields);
         Outcome.Success = (String.IsNullOrEmpty(OutcomeStatusMessage));
         Outcome.FormattedMessage = OutcomeStatusMessage + "\r\n" + OutcomeStatusBody;
         return Outcome;
      }
      //---------------------------------------------------------------------------------------
      private static string ExtractOutcome(Dictionary<string, Object> responseFields) {
         const string SuccessKey = "success";
         Object Value = null;
         if (responseFields.ContainsKey(SuccessKey)) {
            if (responseFields.TryGetValue(SuccessKey, out Value))
               return "Outcome success : " + (bool)Value;
            else
               return "";
         }
         else {
            return "";
         }
      }
      //---------------------------------------------------------------------------------------
      private static string ExtractResponseValues(Dictionary<string , Object> responseFields) {
         const string ErrorKey = "error";
         const string ResultKey = "result";
         StringBuilder Values = new StringBuilder("");
         Values.Append("\r\n");
         Values.Append(ExtractValues(ErrorKey, responseFields));
         Values.Append("\r\n");
         Values.Append(ExtractValues(ResultKey, responseFields));
         return Values.ToString();
      }
      //---------------------------------------------------------------------------------------
      private static string ExtractValues(string key, Dictionary<string, Object> responseFields) {
         StringBuilder ResultMessage = new StringBuilder("");
         string ResultValue;

         if (responseFields.ContainsKey(key)) {
            Dictionary<string, Object> Result = (Dictionary<string, Object>)responseFields[key];
            foreach (KeyValuePair<string, Object> Entry in Result) {
               try {
                  ResultValue = (string)Entry.Value;
               }
               catch {
                  ResultValue = "";
               }
               ResultMessage.Append(Entry.Key);
               ResultMessage.Append(" : ");
               ResultMessage.Append(ResultValue);
               ResultMessage.Append("\r\n");
            }
         }
         return ResultMessage.ToString();
      }
      //---------------------------------------------------------------------------------------
      public static Dictionary<string, Object> Deserialise(string value) {
         JavaScriptSerializer Serializer = new JavaScriptSerializer();
         Dictionary<string, Object> Dictionary;
         value = value.Replace("__", " ");
         Dictionary = (Dictionary<string, Object>)Serializer.DeserializeObject(value);
         return Dictionary;
      }
   }
}