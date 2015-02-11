using System;
using System.Net;
using System.IO;
using System.Text;

namespace DataGovAuUpload {
   sealed class DataGovManager {
      private  DataGovManager() {
      }
      //---------------------------------------------------------------------------------------
      public static DataGovOutcome UploadToDataGov(DataGovParameters parameters) {
         DataGovOutcome Outcome = null;
         try {
            Outcome = Postfile(parameters);
            if (Outcome.Success) {
               return ResponseInterpreter.InterpretResponse(Outcome.PostStatus);
            }
            else
               return Outcome;
         }
         catch {
            throw;
         }
      }
      /*--------------------------------------------------------------------------------------- 
       * Upload a new version of the document as a multipart/form-data
       ---------------------------------------------------------------------------------------*/
      private static DataGovOutcome Postfile(DataGovParameters parameters) {
         // Boundary is a unique string used to deliminate form values sent in the request
         string Boundary = "---------------------------" + DateTime.Now.Ticks.ToString("x");
         byte[] BoundaryBytes = Encoding.ASCII.GetBytes("\r\n--" + Boundary + "\r\n");

         HttpWebRequest PostRequest = null;
         Stream PostStream = null;
         DataGovOutcome Outcome = new DataGovOutcome();
         try {
            PostRequest = ConfigureUploadRequest(parameters, Boundary);
            PostStream = PostRequest.GetRequestStream();
            PostStandardFormHeader(PostStream, BoundaryBytes, parameters);
            PostFilenameHeader(PostStream, BoundaryBytes, parameters.Filename);
            PostFileContentsData(PostStream, parameters.Filename);
            PostTrailerData(PostStream, Boundary);
            Outcome.PostStatus = ReadPostResponse(PostRequest);
            Outcome.Success = true;
         } // The web response may contain more information about the exception
         catch (WebException ex) {
            if (ex.Response != null) {
               using (Stream ErrorStream = ex.Response.GetResponseStream())
                  using (StreamReader reader = new StreamReader(ErrorStream)) {
                     Outcome.PostStatus = reader.ReadToEnd();
                     Outcome.Success = false;
                     return Outcome;
                  }
            }
            else {
               throw;
            }
         }
         catch  {
            throw;
         }
         finally {
            if (PostStream != null) {
               PostStream.Close();
               PostRequest = null;
            }
         }
         return Outcome;
      }
      /*--------------------------------------------------------------------------------------- 
       * Configure request as multipart/form-data 
       * ---------------------------------------------------------------------------------------*/
      private static HttpWebRequest ConfigureUploadRequest(DataGovParameters parameters, string boundary) {
         HttpWebRequest PostRequest = (HttpWebRequest)WebRequest.Create(parameters.CkanUrlUpdate);
         // Content type is  multipart/form-data and includes the boundary information
         PostRequest.ContentType = "multipart/form-data; boundary=" + boundary;
         PostRequest.Method = "POST";
         PostRequest.KeepAlive = true;
         PostRequest.Credentials = System.Net.CredentialCache.DefaultCredentials;
         // Include Authorization and your API key in the request header 
         PostRequest.Headers.Add("Authorization", parameters.ApiKey);
         PostRequest.Timeout = WebConfigSettings.TimeoutMilliseconds;
         return PostRequest;
      }
      /*--------------------------------------------------------------------------------------- 
       * The standard form value is written to the request stream as:
       *    Encoded boundary comprising 2 dashes, boundary, CRLF 
       *    Content-Disposition: form-data; name=<resource Id>
       *    2 CRLFs
       *    The value of the form field
       *    1 CRLF
       * ---------------------------------------------------------------------------------------*/
      private static void PostStandardFormHeader(Stream postStream, byte[] boundaryBytes, DataGovParameters parameters) {
         string Template = "Content-Disposition: form-data; name=\"{0}\"\r\n\r\n{1}";
         foreach (string key in parameters.NameValueCollection.Keys) {
            postStream.Write(boundaryBytes, 0, boundaryBytes.Length);
            string FormItem = string.Format(Template, key, parameters.NameValueCollection[key]);
            byte[] FormItemBytes = Encoding.UTF8.GetBytes(FormItem);
            postStream.Write(FormItemBytes, 0, FormItemBytes.Length);
         }
      }
      /*--------------------------------------------------------------------------------------- 
       * Before uploading the file contents, the file name is written to the request stream as:
       *    Encoded boundary comprising 2 dashes, boundary, CRLF 
       *    Content-Disposition: form-data; name=<action> (in this case "upload")
       *    filename="filename"; 
       *    2 CRLFs
       *    The value of the form field
       *    1 CRLF
       *    Content type
       *    2 CRLFs
       * ---------------------------------------------------------------------------------------*/
      private static void PostFilenameHeader(Stream postStream, byte[] boundaryBytes,  string filename) {
         const string Action = "upload";
         string Template = "Content-Disposition: form-data; name=\"{0}\"; filename=\"{1}\"\r\nContent-Type: {2}\r\n\r\n";
         string Header = string.Format(Template, Action, filename, DataGovParameters.MimeType);
         byte[] HeaderBytes = Encoding.UTF8.GetBytes(Header);
         postStream.Write(boundaryBytes, 0, boundaryBytes.Length);
         postStream.Write(HeaderBytes, 0, HeaderBytes.Length);
      }
      /*--------------------------------------------------------------------------------------- 
       * Wite the contents of the file to the request stream:
       *---------------------------------------------------------------------------------------*/
      private static void PostFileContentsData(Stream postStream, string filename) {
         FileStream FileStream = null;
         try {
            byte[] Buffer = new byte[4096];
             FileStream = new FileStream(filename, FileMode.Open, FileAccess.Read);
            int BytesRead = FileStream.Read(Buffer, 0, Buffer.Length);
            while (BytesRead > 0) {
               postStream.Write(Buffer, 0, BytesRead);
               BytesRead = FileStream.Read(Buffer, 0, Buffer.Length);
            }
         }
         finally {
            if (FileStream !=null) {
               FileStream.Close();
            }
         }
      }
      /*--------------------------------------------------------------------------------------- 
       * To end of the request, write to the request stream:
       *    2 dashes
       *    boundary
       *    2 dashes
       *--------------------------------------------------------------------------------------*/
      private static void PostTrailerData(Stream postStream, string boundary) {
         byte[] Trailer = Encoding.ASCII.GetBytes("\r\n--" + boundary + "--\r\n");
         postStream.Write(Trailer, 0, Trailer.Length);
      }
      //---------------------------------------------------------------------------------------
      private static string ReadPostResponse(HttpWebRequest postRequest) {
         WebResponse PostResponse = null;
         StreamReader StreamReader = null;
         try {
            PostResponse = postRequest.GetResponse();
            Stream PostStream = PostResponse.GetResponseStream();
            StreamReader = new StreamReader(PostStream);
            return (StreamReader.ReadToEnd());
         }
         finally {
            if (StreamReader !=null) {
               StreamReader.Close();
               StreamReader = null;
            }
            if (PostResponse!=null) {
               PostResponse.Close();
               PostResponse = null;
            }
         }
      }
   }
}