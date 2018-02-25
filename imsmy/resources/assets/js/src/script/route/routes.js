import React from 'react'
import {Router, hashHistory, browserHistory, Route, IndexRedirect,IndexRoute,Redirect } from 'react-router'
import Login from '../component/login/Login'
import Index from '../component/Index'
//顶部导航容器
//从左至右 : 站务 - 内容 - 素材 - 广告 -用户 -财务 - 移动端 - 系统设置
import { Station, Contents, SourceMaterial, Advert, User, Finance, Mobile, SystemSet} from '../container'
//站务下的子路由
import { Manage, Screen, NotPass, Race, Material, Audit, Apply, Emcee, Complain, Shieldset, Retrieve } from '../container/station'
import { VideoCensor, VideoPending, VideoScreen } from '../container/station'
import { Detail } from '../container'
//内容下的子路由
import { HostSearch } from '../container/contents'
//素材下的子路由
// 现在是:平台看板 - 模板(全部 - 分类) - 片段(全部 -推荐-分类-搜索热点-上传片段-屏蔽仓)- 混合资源库
import { Platform, TemplateAll, TemplateSort, FragmentAll, FragmentRecommend, FragmentClassify,FragmentSearch, FragmentUpload, FragmentScreen, LibraryUpload } from '../container/sourceMaterial'
//用户页下的子路有
//监控 - 用户管理
import { UserMonitor, ManageAll, ManageThird,ManageMechanism,ManageVip,ManageFramer,ManageCensor,ManageVerify,ManageChoice,ManageFrozen } from '../container/user'

//移动端的路由 (设备终端 - 滤镜（全部 - 发布） )
import { Terminal, FilterAll, FilterRelease } from '../container/mobile'

export default(
  <Router onUpdate={()=>window.scrollTo(0,0)} history={hashHistory}>
    <Route breadcrumbName="当前位置" path="/" component={Index}>
        <IndexRedirect to="/station" />
        {/*站务页*/}
        <Route breadcrumbName="站务" path="station" component={Station}>
          <IndexRedirect to="/manage" />
          <Route breadcrumbName="管理信息" path="/manage" component={Manage} />
          <Route breadcrumbName="视频" path="stationVideo">
            <IndexRedirect to="/videoCensor" />
            <Route breadcrumbName="视频审查" path="/videoCensor" component={VideoCensor} />
            <Route breadcrumbName="待定池" path="/videoPending" component={VideoPending} />
            <Route breadcrumbName="未推荐" path="/notPass" component={NotPass} />
            <Route breadcrumbName="屏蔽仓" path="/videoScreen" component={VideoScreen} />
            <Route breadcrumbName="视频详情" path="/:type/detail/:id" component={Detail} />
          </Route>
          <Route breadcrumbName="竞赛" path="stationRace">
            <IndexRedirect to="/race" />
            <Route breadcrumbName="竞赛审核" path="/race" component={Race} />
            <Route breadcrumbName="未推荐" path="/notPass" component={NotPass} />
            <Route breadcrumbName="屏蔽仓" path="/screen" component={Screen} />
          </Route>
          <Route breadcrumbName="素材" path="stationMaterial">
            <IndexRedirect to="/material" />
            <Route breadcrumbName="素材审查" path="/material" component={Material} />
            <Route breadcrumbName="未推荐" path="/notPass" component={NotPass} />
            <Route breadcrumbName="屏蔽仓" path="/screen" component={Screen} />
          </Route>
          <Route breadcrumbName="认证审核" path="/audit" component={Audit} />
          <Route breadcrumbName="提现申请" path="/apply" component={Apply} />
          <Route breadcrumbName="主持人申请" path="/emcee" component={Emcee} />
          <Route breadcrumbName="投诉与反馈" path="/complain" component={Complain} />
          <Route breadcrumbName="屏蔽理由设置" path="/shieldset" component={Shieldset} />
          <Route breadcrumbName="回收站" path="/retrieve" component={Retrieve} />
        </Route>
        {/*内容页*/}
        <Route breadcrumbName="内容" path="contents" component={Contents}>
            <IndexRedirect to="/host-search" />
            <Route breadcrumbName="热点搜索" path="/host-search" component={HostSearch} />
        </Route>
        {/*素材页*/}
        <Route breadcrumbName="素材" path="sourceMaterial" component={SourceMaterial}>
          <IndexRedirect to="/platform" />
          <Route breadcrumbName="平台看板" path="/platform" component={Platform} />
          <Route breadcrumbName="模板" path="MaterialTemplate">
            <IndexRedirect to="/templateAll" />
            <Route breadcrumbName="全部" path="/templateAll" component={TemplateAll} />
            <Route breadcrumbName="分类" path="/templateSort" component={TemplateSort} />
          </Route>
          <Route breadcrumbName="片段" path="MaterialFragment">
            <IndexRedirect to="/fragmentAll" />
            <Route breadcrumbName="全部" path="/fragmentAll" component={FragmentAll} />
            <Route breadcrumbName="推荐" path="/fragmentRecommend" component={FragmentRecommend} />
            <Route breadcrumbName="分类" path="/fragmentClassify" component={FragmentClassify} />
            <Route breadcrumbName="搜索热点" path="/fragmentSearch" component={FragmentSearch} />
            <Route breadcrumbName="上传片段" path="/fragmentUpload" component={FragmentUpload} />
            <Route breadcrumbName="屏蔽仓" path="/fragmentScreen" component={FragmentScreen} />
          </Route>
          <Route breadcrumbName="混合资源库" path="MaterialLibrary">
            <IndexRedirect to="/libraryUpload" />
            {/* <Route breadcrumbName="全部" path="/libraryAll" component={LibraryAll} />
            <Route breadcrumbName="推荐" path="/libraryRecommend" component={LibraryRecommend} />
            <Route breadcrumbName="分类" path="/libraryClassify" component={LibraryClassify} />
            <Route breadcrumbName="搜索热点" path="/librarySearch" component={LibrarySearch} /> */}
            <Route breadcrumbName="上传资源" path="/libraryUpload" component={LibraryUpload} />
            {/* <Route breadcrumbName="屏蔽仓" path="/libraryScreen" component={LibraryScreen} /> */}
          </Route>
        </Route>
          {/*广告页*/}
        <Route breadcrumbName="广告" path="advert" component={Advert}>

        </Route>
          {/*用户页*/}
        <Route breadcrumbName="用户" path="user" component={User}>
          <IndexRedirect to="/userMonitor" />
          <Route breadcrumbName="监控页" path="/userMonitor" component={UserMonitor} />
          <Route breadcrumbName="用户管理" path="userManage">
            <IndexRedirect to="/manageAll" />
            <Route breadcrumbName="全部" path="/manageAll" component={ManageAll} />
            <Route breadcrumbName="第三方" path="/manageThird" component={ManageThird} />
            <Route breadcrumbName="机构" path="/manageMechanism" component={ManageMechanism} />
            <Route breadcrumbName="VIP" path="/manageVip" component={ManageVip} />
            <Route breadcrumbName="创作者" path="/manageFramer" component={ManageFramer} />
            <Route breadcrumbName="审查者" path="/manageCensor" component={ManageCensor} />
            <Route breadcrumbName="认证用户" path="/manageVerify" component={ManageVerify} />
            <Route breadcrumbName="精选用户" path="/manageChoice" component={ManageChoice} />
            <Route breadcrumbName="冻结仓" path="/manageFrozen" component={ManageFrozen} />
          </Route>
        </Route>
          {/*财务页*/}
        <Route breadcrumbName="财务" path="finance" component={Finance}>

        </Route>
          {/*移动端页*/}
        <Route breadcrumbName="移动端" path="mobile" component={Mobile}>
          <IndexRedirect to="/terminal" />
          <Route breadcrumbName="设备终端" path="/terminal" component={Terminal} />
          <Route breadcrumbName="滤镜" path="MobileFilter">
            <IndexRedirect to="/filterAll" />
            <Route breadcrumbName="全部" path="/filterAll" component={FilterAll} />
            <Route breadcrumbName="发布滤镜" path="/filterRelease" component={FilterRelease} />
          </Route>

        </Route>
          {/*系统设置页*/}
        <Route breadcrumbName="系统设置" path="systemSet" component={SystemSet}>

        </Route>
      </Route>
      <Route path='login' component={Login} />

  </Router>
)
