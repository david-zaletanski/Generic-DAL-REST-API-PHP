/*
 *  RestAPI.playground
 *  Friday May 15, 2015
 *  How to submit HTTP requests to the Trackmini API service.
 */

// To submit HTTP requests, use NSURL, NSURLRequest, NSURLSession and NSURLConnection
// NSURLSession is preferred for iOS 7 and later.

import UIKit
import Foundation
import XCPlayground

// Let asynchronous code run in playground.
XCPSetExecutionShouldContinueIndefinitely()

let goog_URL = "http://www.google.com/"
let tmAPI_item_URL = "http://trackula.me/trackmini/api/item"

// Create the endpoint URL.
let url = NSURL(string: tmAPI_item_URL)
if url == nil {
    println("URL IS NIL")
}

// Create a task to execute the request.
/*let task = NSURLSession.sharedSession().dataTaskWithURL(url!) {
    
    // completionHandler receives response...
    (data, response, error) -> Void in
    println("Received a response!")
    if error != nil {
        println("An error occured \(error.localizedDescription): \(error.userInfo)")
    } else if data != nil {
        println(NSString(data: data, encoding: NSUTF8StringEncoding))
    } else {
        println("Did not receive data response.")
    }
    
}

// Run the task.
task.resume()*/

extension String {
    // Add a convert string to URL encoded string.
    func stringByURLEncoding() -> String? {
        let charSet = NSMutableCharacterSet.alphanumericCharacterSet()
        charSet.addCharactersInString("-._~")
        return self.stringByAddingPercentEncodingWithAllowedCharacters(charSet)
    }
}
extension Dictionary {
    // Add a convert to URL encoded HTTP parameters string.
    func stringFromHttpParameters() -> String {
        let paramArray = map(self) { (key, value) -> String in
            let pctEscapedKey = (key as! String).stringByURLEncoding()!
            let pctEscapedValue = (value as! String).stringByURLEncoding()!
            println("\(pctEscapedKey)=\(pctEscapedValue)")
            return "\(pctEscapedKey)=\(pctEscapedValue)"
        }
        return join("&", paramArray)
    }
}

class HttpTransaction {
    
    let request: HttpRequest
    
    init(request: HttpRequest) {
        self.request = request
    }
    
}

class HttpRequest {
    
    func sendRequest(url: NSURL, params: [String: AnyObject]) {
        println("SENDING GET REQUEST")
        sendRequest(url, params: params, completionHandler: handleResponse)
    }
    func sendRequest(url: NSURL, params: [String: AnyObject], completionHandler: ((NSData!, NSURLResponse!, NSError!) -> Void)?) {
        // Create the request, append GET parameters.
        //let requestURL = NSURL(string: "\(url)?\ (params.stringFromHttpParameters())")!
        let requestURL = url
        println(requestURL)
        var request = NSMutableURLRequest(URL: requestURL)
        request.HTTPMethod = "GET"      // Set the HTTP method.
        
        let session = NSURLSession.sharedSession()
        let task = session.dataTaskWithRequest(request, completionHandler: completionHandler)
        println("SENDING REQUEST")
        task.resume()
    }
    
    func handleResponse(data: NSData!, response: NSURLResponse!, err: NSError!) -> Void {
        println("RECEIVED RESPONSE")
        if err != nil {
            println(err?.description)
        } else if data != nil {
            var jsonResult: NSDictionary = NSJSONSerialization.JSONObjectWithData(data, options: NSJSONReadingOptions.MutableContainers, error: nil) as! NSDictionary
            println(jsonResult)
        } else {
            println("Returned data was nil.")
        }
    }
    
    class func urlEncodeString(string originalString: String, urlType: URLAllowedCharacterSets) -> String {
        var customAllowedSet = NSCharacterSet(charactersInString: urlType.rawValue).invertedSet
        var escapedString = originalString.stringByAddingPercentEncodingWithAllowedCharacters(customAllowedSet)
    }
    
    /// Sets of characters that must be escaped depending on the type of URL.
    enum URLAllowedCharacterSets: String {
        //case URLFragmentAllowedCharacterSet = "#%<>[\\]^`{|}" // Note: Duplicate value of URLUserAllowed...
        case URLHostAllowedCharacterSet = "#%/<>?@\\^`{|}"
        case URLPasswordAllowedCharacterSet = "#%/:<>?@[\\]^`{|}"
        case URLPathAllowedCharacterSet = "#%;<>?[\\]^`{|}"
        case URLQueryAllowedCharacterSet = "#%<>[\\]^`{|}"
        case URLUserAllowedCharacterSet = "#%/:<>?@[\\]^`"
    }
}

var request = HttpRequest()
var params = [String: AnyObject]()
params.updateValue(1, forKey: "id")
println(params.stringFromHttpParameters())
request.sendRequest(url!, params: params)
