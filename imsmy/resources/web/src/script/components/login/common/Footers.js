import React, { Component } from 'react'
import { Layout } from 'antd'
const { Footer } = Layout

class Footers extends Component{
  render(){
    return(
      <Footer className="index_footer_box">
        <span className="footer_copy">Copyright ©  Hivideo.com All Rights Reserved | 京ICP备15066211号</span>
        <div className="footer_link_box">
          {/* <span><img src="./img/goobird_logo.png" alt="谷鸟科技"/></span> */}
          {/* <span><img src="./img/hivideo_logo.png" alt="嗨视频"/></span> */}
           <span><img src="http://img.cdn.hivideo.com/web/img/goobird_logo.png" alt="谷鸟科技"/></span>
           <span><img src="http://img.cdn.hivideo.com/web/img/hivideo_logo.png" alt="嗨视频"/></span>
        </div>
      </Footer>
    )
  }
}

export default Footers
